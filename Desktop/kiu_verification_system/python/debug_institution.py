import sys
sys.path.insert(0, 'python')
from ocr import extract_text
from validators.rules import extract_fields

text = extract_text('uploads/admission_letters/69bd48a487a58_1774012580.pdf')
fields = extract_fields(text, 'admission_letter')
print('INSTITUTION RAW REPR:')
print(repr(fields.get('institution')))
print('\nEXTRACTED FIELDS:')
print(fields)
