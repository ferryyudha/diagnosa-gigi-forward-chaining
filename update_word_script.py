import re

with open('c:/xampp/htdocs/forward_chaining/generate_word.py', 'r', encoding='utf-8') as file:
    content = file.read()

# Replace filenames
content = content.replace('Hasil_dan_Pembahasan_SiPaGi_Final.docx', 'Hasil_dan_Pembahasan_Diagnosa_Penyakit_Gigi_Metode_Forward_Chaining.docx')

# Replace names
content = re.sub(r'SiPaGi \(Sistem Pakar Penyakit Gigi\)', 'Diagnosa Penyakit Gigi Metode Forward Chaining', content)
content = re.sub(r'Sistem Pakar Penyakit Gigi \(SiPaGi\)', 'Diagnosa Penyakit Gigi Metode Forward Chaining', content)
content = re.sub(r'sistem pakar SiPaGi', 'aplikasi Diagnosa Penyakit Gigi Metode Forward Chaining', content)
content = re.sub(r'aplikasi SiPaGi', 'aplikasi Diagnosa Penyakit Gigi Metode Forward Chaining', content)
content = re.sub(r'SiPaGi', 'Diagnosa Penyakit Gigi Metode Forward Chaining', content)
content = re.sub(r'Sistem Pakar Penyakit Gigi', 'Diagnosa Penyakit Gigi Metode Forward Chaining', content)

with open('c:/xampp/htdocs/forward_chaining/generate_word.py', 'w', encoding='utf-8') as file:
    file.write(content)

print("Replacement complete!")
