import sys
sys.path.insert(0, 'python')
import verify
res = verify.verify_file('uploads/admission_letters/69bd48a487a58_1774012580.pdf', 'admission_letter')
import json
print(json.dumps(res, ensure_ascii=False, indent=2))
