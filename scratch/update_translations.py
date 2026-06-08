import json

en_dict = json.load(open(r"c:\Kuliah Telkom University\websiteMusicTerbaik\lang\en.json", "r"))
id_dict = json.load(open(r"c:\Kuliah Telkom University\websiteMusicTerbaik\lang\id.json", "r"))

new_en = {
    "You are not signed in": "You are not signed in",
    "Sign in to view your account settings.": "Sign in to view your account settings.",
    "Profile Information": "Profile Information",
    "Username cannot be changed": "Username cannot be changed",
    "Email cannot be changed": "Email cannot be changed",
    "Save Changes": "Save Changes",
    "Update Password": "Update Password",
    "Ensure your account is using a long, random password to stay secure.": "Ensure your account is using a long, random password to stay secure.",
    "Current Password": "Current Password",
    "New Password": "New Password",
    "Confirm New Password": "Confirm New Password",
    "Danger Zone": "Danger Zone",
    "Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.": "Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.",
    "Delete Account": "Delete Account",
    "Are you sure you want to delete your account?": "Are you sure you want to delete your account?",
    "Please enter your password to confirm you would like to permanently delete your account.": "Please enter your password to confirm you would like to permanently delete your account.",
    "Cancel": "Cancel",
    "Profile updated successfully.": "Profile updated successfully.",
    "Password updated successfully.": "Password updated successfully.",
    "The provided password does not match your current password.": "The provided password does not match your current password."
}

new_id = {
    "You are not signed in": "Anda belum masuk",
    "Sign in to view your account settings.": "Masuk untuk melihat pengaturan akun Anda.",
    "Profile Information": "Informasi Profil",
    "Username cannot be changed": "Nama pengguna tidak dapat diubah",
    "Email cannot be changed": "Email tidak dapat diubah",
    "Save Changes": "Simpan Perubahan",
    "Update Password": "Ganti Kata Sandi",
    "Ensure your account is using a long, random password to stay secure.": "Pastikan akun Anda menggunakan kata sandi panjang dan acak agar tetap aman.",
    "Current Password": "Kata Sandi Saat Ini",
    "New Password": "Kata Sandi Baru",
    "Confirm New Password": "Konfirmasi Kata Sandi Baru",
    "Danger Zone": "Zona Bahaya",
    "Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.": "Setelah akun Anda dihapus, semua sumber daya dan datanya akan dihapus secara permanen. Sebelum menghapus akun, pastikan Anda telah mengunduh semua data atau informasi yang ingin Anda simpan.",
    "Delete Account": "Hapus Akun",
    "Are you sure you want to delete your account?": "Apakah Anda yakin ingin menghapus akun Anda?",
    "Please enter your password to confirm you would like to permanently delete your account.": "Silakan masukkan kata sandi Anda untuk mengonfirmasi penghapusan akun secara permanen.",
    "Cancel": "Batal",
    "Profile updated successfully.": "Profil berhasil diperbarui.",
    "Password updated successfully.": "Kata sandi berhasil diperbarui.",
    "The provided password does not match your current password.": "Kata sandi yang dimasukkan tidak cocok dengan kata sandi saat ini."
}

en_dict.update(new_en)
id_dict.update(new_id)

json.dump(en_dict, open(r"c:\Kuliah Telkom University\websiteMusicTerbaik\lang\en.json", "w"), indent=4)
json.dump(id_dict, open(r"c:\Kuliah Telkom University\websiteMusicTerbaik\lang\id.json", "w"), indent=4)

print("DONE")
