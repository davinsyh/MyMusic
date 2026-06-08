import json
from ytmusicapi import YTMusic
ytmusic = YTMusic()
h = ytmusic.get_home()
plist = next((x['contents'][0]['playlistId'] for x in h if 'contents' in x and len(x['contents']) > 0 and 'playlistId' in x['contents'][0]), None)
print('Playlist ID:', plist)
if plist:
    p = ytmusic.get_playlist(plist)
    print(list(p.keys()))
