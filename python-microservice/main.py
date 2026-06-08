from fastapi import FastAPI, HTTPException, Query
from ytmusicapi import YTMusic
import redis
import json
import logging

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI(title="Music Streaming Microservice")
ytmusic = YTMusic()

# Redis configuration (optional for now, fallback if not available)
try:
    redis_client = redis.Redis(host='localhost', port=6379, db=0, decode_responses=True)
    redis_client.ping()
    logger.info("Connected to Redis successfully.")
except Exception as e:
    logger.warning(f"Failed to connect to Redis. Running without cache. Error: {e}")
    redis_client = None

def get_cached(key):
    if redis_client:
        try:
            val = redis_client.get(key)
            if val:
                return json.loads(val)
        except Exception:
            pass
    return None

def set_cached(key, value, ex=3600):
    if redis_client:
        try:
            redis_client.set(key, json.dumps(value), ex=ex)
        except Exception:
            pass

@app.get("/search")
def search_music(q: str = Query(..., min_length=1)):
    cache_key = f"search:{q}"
    cached_data = get_cached(cache_key)
    if cached_data:
        return {"source": "cache", "data": cached_data}

    try:
        results = ytmusic.search(q)
        # Extract relevant fields
        parsed_results = []
        for r in results:
            result_type = r.get('resultType', 'song')
            
            item = {
                "type": result_type,
                "title": r.get('title', r.get('artist', 'Unknown')),
                "artist": r['artists'][0]['name'] if r.get('artists') else (r.get('author', 'Unknown Artist')),
                "thumbnail": r['thumbnails'][-1]['url'] if r.get('thumbnails') else '',
                "duration": r.get('duration', ''),
            }

            if result_type in ['song', 'video', 'episode']:
                item['id'] = r.get('videoId')
                item['type'] = 'video'
            elif result_type in ['album', 'playlist', 'podcast']:
                item['id'] = r.get('browseId') or r.get('playlistId')
                if not item['id']: continue
            elif result_type == 'artist':
                item['id'] = r.get('browseId')
                if not item['id']: continue
            else:
                continue

            # Only append if we have a valid ID
            if item.get('id'):
                parsed_results.append(item)
        
        set_cached(cache_key, parsed_results)
        return {"source": "api", "data": parsed_results}
    except Exception as e:
        logger.error(f"Search error: {e}")
        raise HTTPException(status_code=500, detail=str(e))

import yt_dlp

@app.get("/track/{video_id}")
def get_track(video_id: str):
    # For track details, we might want to cache it too.
    cache_key = f"track:{video_id}"
    cached_data = get_cached(cache_key)
    if cached_data:
        return {"source": "cache", "data": cached_data}

    try:
        PIPED_INSTANCES = [
            "https://pipedapi.kavin.rocks",
            "https://pipedapi.tokhmi.xyz",
            "https://pipedapi.adminforge.de",
            "https://api.piped.projectsegfau.lt",
            "https://pipedapi.smnz.de"
        ]
        
        stream_url = None
        for instance in PIPED_INSTANCES:
            try:
                res = requests.get(f"{instance}/streams/{video_id}", timeout=5)
                if res.status_code == 200:
                    stream_data = res.json()
                    audio_streams = stream_data.get('audioStreams', [])
                    if audio_streams:
                        audio_streams.sort(key=lambda x: x.get('bitrate', 0), reverse=True)
                        stream_url = audio_streams[0]['url']
                        break
            except Exception as e:
                logger.warning(f"Failed with {instance}: {e}")
                continue
        
        song_info = ytmusic.get_song(video_id)
        
        # Kita langsung berikan Piped URL ke frontend agar tidak membebani server AWS
        data = {
            "id": video_id,
            "title": song_info['videoDetails']['title'],
            "author": song_info['videoDetails']['author'],
            "thumbnail": song_info['videoDetails']['thumbnail']['thumbnails'][-1]['url'],
            "stream_url": stream_url
        }
        
        set_cached(cache_key, data)
        return {"source": "api", "data": data}
    except Exception as e:
        logger.error(f"Track error: {e}")
        raise HTTPException(status_code=500, detail=str(e))

from fastapi.responses import StreamingResponse
import requests

from fastapi import Request

@app.get("/stream/{video_id}")
def stream_audio(video_id: str, request: Request):
    try:
        PIPED_INSTANCES = [
            "https://pipedapi.kavin.rocks",
            "https://pipedapi.tokhmi.xyz",
            "https://pipedapi.adminforge.de",
            "https://api.piped.projectsegfau.lt",
            "https://pipedapi.smnz.de"
        ]
        
        url = None
        for instance in PIPED_INSTANCES:
            try:
                res = requests.get(f"{instance}/streams/{video_id}", timeout=5)
                if res.status_code == 200:
                    stream_data = res.json()
                    audio_streams = stream_data.get('audioStreams', [])
                    if audio_streams:
                        audio_streams.sort(key=lambda x: x.get('bitrate', 0), reverse=True)
                        url = audio_streams[0]['url']
                        break
            except Exception:
                continue
            
        if not url:
            raise HTTPException(status_code=404, detail="Stream URL not found")
            
        # Pass the Range header if requested by the browser
        headers = {}
        if "range" in request.headers:
            headers["Range"] = request.headers["range"]
            
        # Stream the response using requests
        req = requests.get(url, headers=headers, stream=True)
        
        # Forward relevant headers back to the client
        response_headers = {}
        for h in ["Content-Range", "Accept-Ranges", "Content-Length"]:
            if h in req.headers:
                response_headers[h] = req.headers[h]
                
        return StreamingResponse(
            req.iter_content(chunk_size=8192), 
            status_code=req.status_code,
            headers=response_headers,
            media_type=req.headers.get("Content-Type", "audio/webm")
        )
    except Exception as e:
        logger.error(f"Stream proxy error: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/charts")
def get_charts():
    cache_key = "charts:top"
    cached_data = get_cached(cache_key)
    if cached_data:
        return {"source": "cache", "data": cached_data}

    try:
        # Get charts for ID (Indonesia) by searching Top Hits
        songs = ytmusic.search("Top Hits Indonesia", filter="songs")
        
        parsed_results = []
        for r in songs[:20]: # Limit to 20
            if r['resultType'] == 'song':
                parsed_results.append({
                    "id": r['videoId'],
                    "title": r['title'],
                    "artist": r['artists'][0]['name'] if r.get('artists') else 'Unknown Artist',
                    "thumbnail": r['thumbnails'][-1]['url'] if r.get('thumbnails') else '',
                })
            
        set_cached(cache_key, parsed_results)
        return {"source": "api", "data": parsed_results}
    except Exception as e:
        logger.error(f"Charts error: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/trending-shorts")
def get_trending_shorts():
    cache_key = "trending:shorts"
    cached_data = get_cached(cache_key)
    if cached_data:
        return {"source": "cache", "data": cached_data}

    try:
        home_data = ytmusic.get_home()
        shorts_section = next((x for x in home_data if x.get('title') == 'Trending in Shorts'), None)
        
        parsed_results = []
        if shorts_section and 'contents' in shorts_section:
            for r in shorts_section['contents']:
                parsed_results.append({
                    "id": r['videoId'],
                    "title": r['title'],
                    "artist": r['artists'][0]['name'] if r.get('artists') else 'Unknown Artist',
                    "thumbnail": r['thumbnails'][-1]['url'] if r.get('thumbnails') else '',
                })
            
        set_cached(cache_key, parsed_results)
        return {"source": "api", "data": parsed_results}
    except Exception as e:
        logger.error(f"Trending shorts error: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/home")
def get_home_sections():
    cache_key = "home:sections"
    cached_data = get_cached(cache_key)
    if cached_data:
        return {"source": "cache", "data": cached_data}

    try:
        home_data = ytmusic.get_home()
        sections = []
        for section in home_data:
            if 'title' not in section or 'contents' not in section:
                continue
            
            parsed_contents = []
            for r in section['contents']:
                if 'videoId' in r:
                    parsed_contents.append({
                        "id": r['videoId'],
                        "type": "video",
                        "title": r.get('title', ''),
                        "artist": r['artists'][0]['name'] if r.get('artists') else 'Unknown',
                        "thumbnail": r['thumbnails'][-1]['url'] if r.get('thumbnails') else '',
                    })
                elif 'playlistId' in r:
                    parsed_contents.append({
                        "id": r['playlistId'],
                        "type": "playlist",
                        "title": r.get('title', ''),
                        "artist": r.get('description', 'Playlist'),
                        "thumbnail": r['thumbnails'][-1]['url'] if r.get('thumbnails') else '',
                    })
                elif 'browseId' in r:
                    parsed_contents.append({
                        "id": r['browseId'],
                        "type": "album",
                        "title": r.get('title', ''),
                        "artist": r.get('subtitle', 'Album'),
                        "thumbnail": r['thumbnails'][-1]['url'] if r.get('thumbnails') else '',
                    })
            if parsed_contents:
                sections.append({
                    "title": section['title'],
                    "contents": parsed_contents
                })
            
        set_cached(cache_key, sections)
        return {"source": "api", "data": sections}
    except Exception as e:
        logger.error(f"Home sections error: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/playlist/{playlist_id}")
def get_playlist_details(playlist_id: str):
    cache_key = f"playlist:{playlist_id}"
    cached_data = get_cached(cache_key)
    if cached_data:
        return {"source": "cache", "data": cached_data}

    try:
        # YouTube sometimes prepends 'VL' to playlist-like IDs
        if playlist_id.startswith('VL'):
            playlist_id = playlist_id[2:]

        if playlist_id.startswith('MPREb_') or playlist_id.startswith('browseId'):
            raw = ytmusic.get_album(playlist_id)
        elif playlist_id.startswith('UC'):
            raw = ytmusic.get_artist(playlist_id)
            # Reformat artist data to mimic a playlist
            songs = raw.get('songs', {}).get('results', []) if isinstance(raw.get('songs'), dict) else []
            singles = raw.get('singles', {}).get('results', []) if isinstance(raw.get('singles'), dict) else []
            raw['tracks'] = songs + singles
            raw['title'] = raw.get('name', 'Artist Profile')
            raw['author'] = "Artist"
            raw['description'] = raw.get('description', '')
        elif playlist_id.startswith('MPSP'):
            raw = ytmusic.get_podcast(playlist_id)
            # Reformat podcast to mimic a playlist
            raw['tracks'] = raw.get('episodes', [])
            raw['author'] = raw.get('author', {}).get('name', 'Podcast') if isinstance(raw.get('author'), dict) else raw.get('author', 'Podcast')
        else:
            raw = ytmusic.get_playlist(playlist_id)
        
        # Parse tracks
        tracks = []
        for r in raw.get('tracks', []):
            if 'videoId' in r and r['videoId']:
                tracks.append({
                    "id": r['videoId'],
                    "title": r.get('title', ''),
                    "artist": r['artists'][0]['name'] if r.get('artists') else 'Unknown Artist',
                    "thumbnail": r['thumbnails'][-1]['url'] if r.get('thumbnails') else (raw['thumbnails'][-1]['url'] if raw.get('thumbnails') else ''),
                    "duration": r.get('duration', '')
                })

        parsed_data = {
            "id": raw.get('id', playlist_id),
            "title": raw.get('title', ''),
            "description": raw.get('description', ''),
            "author": raw.get('author', {}).get('name', 'Unknown') if isinstance(raw.get('author'), dict) else raw.get('author', ''),
            "trackCount": raw.get('trackCount', len(tracks)),
            "thumbnails": raw['thumbnails'][-1]['url'] if raw.get('thumbnails') else '',
            "tracks": tracks
        }
            
        set_cached(cache_key, parsed_data)
        return {"source": "api", "data": parsed_data}
    except Exception as e:
        logger.error(f"Playlist error: {e}")
        raise HTTPException(status_code=500, detail=str(e))

