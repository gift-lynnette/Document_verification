from __future__ import annotations

import html
import zipfile
from pathlib import Path


OUT = Path("reports/kiu_green_card_report_using_guide.docx")


SECTIONS = [
    ("title", "DESIGN AND IMPLEMENTATION OF AN AUTOMATED TUITION VERIFICATION AND GREEN CARD ISSUANCE SYSTEM FOR KAMPALA INTERNATIONAL UNIVERSITY"),
    ("center", "A Project Report Prepared Following the Provided Report Guide"),
    ("center", "Kampala International University"),
    ("page", ""),
    ("h1", "DECLARATION"),
    ("p", "I declare that this report is my original work and has been prepared for academic purposes. The system described in this report was designed and implemented to address tuition verification, student document review, finance clearance, and digital green card issuance at Kampala International University."),
    ("p", "Student Name: ........................................................"),
    ("p", "Registration Number: ................................................"),
    ("p", "Signature: ................................ Date: ...................."),
    ("page", ""),
    ("h1", "APPROVAL"),
    ("p", "This report has been submitted for examination with the approval of the supervisor."),
    ("p", "Supervisor Name: ...................................................."),
    ("p", "Signature: ................................ Date: ...................."),
    ("page", ""),
    ("h1", "DEDICATION"),
    ("p", "This work is dedicated to my family, lecturers, classmates, and all individuals who supported the successful completion of this project."),
    ("page", ""),
    ("h1", "ACKNOWLEDGEMENT"),
    ("p", "I thank God for the strength and knowledge given throughout the development of this project. I also appreciate my supervisor, lecturers, Kampala International University staff, classmates, and family members for their guidance, encouragement, and support during the research and system implementation process."),
    ("page", ""),
    ("h1", "ABSTRACT"),
    ("p", "This report presents the design and implementation of an automated tuition verification and green card issuance system for Kampala International University. The manual process of verifying student documents, confirming tuition payments, and issuing green cards is associated with delays, repeated office visits, weak tracking, and inconsistent record management. The developed system provides a web-based platform through which students submit academic documents and payment evidence, admissions staff review documents, finance staff confirm payment clearance, and authorized admissions users issue digital green cards."),
    ("p", "The system was implemented using PHP, MySQL, HTML, CSS, JavaScript, and a local Python-based verification component. The Python component supports machine-assisted document review by extracting text, classifying uploaded documents, checking required fields, calculating confidence scores, and producing risk flags. The system improves transparency, traceability, workflow control, and service delivery by centralizing submissions, decisions, notifications, audit logs, reports, and green card verification."),
    ("page", ""),
    ("h1", "TABLE OF CONTENTS"),
    ("p", "Preliminary Pages"),
    ("p", "CHAPTER ONE: GENERAL INTRODUCTION"),
    ("p", "CHAPTER TWO: LITERATURE REVIEW"),
    ("p", "CHAPTER THREE: METHODOLOGY"),
    ("p", "CHAPTER FOUR: SYSTEM ANALYSIS AND DESIGN"),
    ("p", "CHAPTER FIVE: SYSTEM IMPLEMENTATION, TESTING AND EVALUATION"),
    ("p", "CHAPTER SIX: CONCLUSION AND RECOMMENDATION"),
    ("p", "REFERENCES"),
    ("p", "APPENDICES"),
    ("page", ""),
    ("h1", "1.0 CHAPTER ONE: GENERAL INTRODUCTION"),
    ("h2", "1.1 Background of the Study"),
    ("p", "Globally, universities are increasingly adopting digital platforms to improve administrative services such as admissions, registration, fee payment, student identity management, and academic document processing. The use of information systems in higher education has reduced manual paperwork and improved the speed, accuracy, and transparency of institutional workflows. Automated verification systems are especially important where large numbers of students submit documents and payment evidence within limited registration periods."),
    ("p", "In Uganda, universities continue to digitize student services in response to increasing student numbers, the need for better accountability, and the demand for faster service delivery. Manual verification of tuition payments and student documents can create long queues, delayed clearance, duplicated records, and difficulties in tracking the status of each student. Kampala International University requires a reliable way to manage student document submission, finance clearance, and green card issuance in a coordinated workflow."),
    ("p", "At Kampala International University, the green card represents proof that a student has completed required verification and clearance steps. The proposed system therefore focuses on automating the process from student document submission to admissions review, finance clearance, and final green card generation. The system also integrates a Python-based machine-assisted verification component to support document classification, OCR-based extraction, confidence scoring, and risk-flagging."),
    ("h2", "1.2 Statement of the Problem"),
    ("p", "The existing manual process of tuition verification and green card issuance is time-consuming and difficult to monitor. Students may move between offices to submit documents, follow up on payment verification, and collect green cards. Staff may also face challenges such as repeated submissions, missing documents, poor tracking of decisions, inconsistent verification, and limited reporting. These weaknesses can delay registration and reduce service quality."),
    ("p", "Therefore, there is a need for an automated tuition verification and green card issuance system that centralizes student submissions, supports admissions and finance workflows, records decisions, notifies users, and issues verifiable digital green cards."),
    ("h2", "1.3 Objectives"),
    ("h3", "1.3.1 General Objective"),
    ("p", "To design and implement an automated tuition verification and green card issuance system for Kampala International University."),
    ("h3", "1.3.2 Specific Objectives"),
    ("list", "To investigate the current manual tuition verification and green card issuance process at KIU."),
    ("list", "To design a web-based system model for student document submission, admissions verification, finance clearance, and green card issuance."),
    ("list", "To implement the system using PHP, MySQL, and a Python-based document verification component."),
    ("list", "To test and evaluate the developed system in terms of functionality, workflow control, usability, and verification support."),
    ("h3", "1.3.3 Research Questions"),
    ("list", "What challenges exist in the current manual tuition verification and green card issuance process?"),
    ("list", "What system design can support automated document submission, verification, finance clearance, and green card issuance?"),
    ("list", "How can PHP, MySQL, and Python be integrated to support automated document review and workflow control?"),
    ("list", "How effective is the implemented system in improving efficiency, traceability, and service delivery?"),
    ("h2", "1.4 Scope of the Study"),
    ("p", "The geographical scope of the study is Kampala International University. The system scope covers student registration, document submission, admissions review, finance verification, notifications, audit logging, reporting, green card generation, and public green card verification. The content scope also includes machine-assisted document verification using a local Python component for OCR, classification, scoring, and risk-flagging."),
    ("h2", "1.5 Significance of the Study"),
    ("p", "The study is significant to students because it reduces repeated office visits and improves visibility of verification status. It benefits admissions and finance staff by organizing queues, reducing paperwork, and supporting evidence-based decisions. It benefits university administrators by improving reporting, auditability, data consistency, and accountability. Academically, the study contributes to knowledge on practical digital transformation in university administration."),
    ("page", ""),
    ("h1", "2.0 CHAPTER TWO: LITERATURE REVIEW"),
    ("h2", "2.1 Introduction"),
    ("p", "This chapter reviews literature related to automated tuition verification, digital student administration, document verification, machine-assisted review, and digital credential issuance. The review focuses on studies and concepts relevant to the system objectives."),
    ("h2", "2.2 Related Studies"),
    ("p", "Automated student administration systems have become important tools for improving university service delivery. Literature shows that student portals, online registration systems, digital payment platforms, and electronic document management systems reduce administrative workload and improve access to services. These systems support faster submission, centralized storage, and improved communication between students and staff."),
    ("p", "Digital payment verification systems are used to reduce manual reconciliation and improve financial control. In university environments, finance offices often need to confirm whether students have met minimum payment requirements before registration or card issuance. An automated system can support this process by capturing payment evidence, comparing payment values with fee structures, and recording clearance decisions."),
    ("p", "Document verification systems help institutions check whether submitted records are complete, readable, and relevant. Optical Character Recognition can extract text from scanned documents and images, while rule-based or machine-assisted checks can identify missing fields, suspicious terms, or wrong document types. Although automated verification cannot fully replace institutional judgement, it supports staff by highlighting important information and risk indicators."),
    ("p", "Digital student cards and QR-based verification improve authenticity and reduce unauthorized use of printed documents. A digital green card can contain student details, registration number, card number, validity dates, QR code, and verification link. This makes it easier for authorized users to confirm that a card is genuine."),
    ("h2", "2.3 Gaps Identified"),
    ("p", "Existing systems often focus on broad student management but do not directly address the specific workflow of tuition verification, admissions document review, finance clearance, and green card issuance in the KIU context. Some systems also digitize records without providing machine-assisted document verification. This study addresses these gaps by developing a focused system that combines web-based workflow automation with Python-supported document review."),
    ("page", ""),
    ("h1", "3.0 CHAPTER THREE: METHODOLOGY"),
    ("p", "This chapter presents the research approach, target population, sampling techniques, sample size, system development methodology, data collection methods, system design techniques, testing methods, implementation tools, and ethical considerations."),
    ("h2", "3.1 Research Approach"),
    ("p", "The study adopted a mixed-methods approach supported by a case study design. The mixed approach was suitable because the study required both quantitative information about user experiences and qualitative information about workflow challenges. The case study focused on Kampala International University because the system was designed for its tuition verification and green card issuance process."),
    ("h2", "3.2 Target Population"),
    ("p", "The target population included students, admissions staff, finance staff, and administrators involved in document submission, document verification, tuition clearance, and green card issuance. These groups were selected because they interact directly with the process addressed by the system."),
    ("h2", "3.3 Sampling Techniques"),
    ("p", "Purposive sampling was used for admissions, finance, and administrative staff because they possess direct knowledge of the verification workflow. Simple random sampling was considered suitable for students because they are the main users of the submission and status-tracking features."),
    ("h2", "3.4 Sample Size"),
    ("p", "The sample size consisted of selected respondents from the student body and key staff offices. The unit of analysis was the user group involved in tuition verification and green card issuance. The sample provided enough information to understand process delays, required system features, and expected benefits of automation."),
    ("h2", "3.5 System Development Methodologies"),
    ("p", "The system was developed using Agile methodology. Agile was selected because it supports iterative development, user feedback, testing, and gradual improvement. The project was implemented in modules including authentication, student submission, admissions verification, finance clearance, green card issuance, reporting, notifications, audit logging, and Python-based document verification."),
    ("h3", "3.5.1 Data Collection Methods"),
    ("p", "Data was collected using questionnaires, interviews, observation, and document review. Questionnaires gathered student views about manual verification challenges. Interviews captured staff requirements. Observation helped identify the movement of records between offices. Document review identified the forms and files required by the system."),
    ("h3", "3.5.2 System Design and Interface Techniques"),
    ("p", "The system was designed using modular web application principles. The interface was organized according to user roles: student, admissions, finance, and administrator. Workflow diagrams, database design, forms, dashboards, and review screens were used to guide development."),
    ("h3", "3.5.3 Testing"),
    ("p", "Testing included functional testing, integration testing, validation testing, and workflow testing. Functional testing checked individual modules. Integration testing checked the movement of a submission from student upload to green card generation. Validation testing checked required fields, file uploads, dates, programme choices, and payment amounts."),
    ("h3", "3.5.4 Tools for Implementation"),
    ("p", "The system was implemented using PHP, MySQL, HTML, CSS, JavaScript, Apache, and XAMPP. Python was used for document verification through OCR-supported text extraction, classification, field extraction, confidence scoring, and risk-flagging. Tesseract OCR and Poppler support document text extraction where available."),
    ("h2", "3.6 Logistical and Ethical Considerations"),
    ("p", "The system handles personal, academic, and payment information. Therefore, user authentication, role-based access, CSRF protection, file validation, audit logging, and controlled storage were considered important. Automated verification was designed to support staff decisions rather than unfairly reject students without human review."),
    ("page", ""),
    ("h1", "CHAPTER FOUR: SYSTEM ANALYSIS AND DESIGN"),
    ("h2", "4.1 Existing System Analysis"),
    ("p", "The existing process is largely manual and depends on students submitting documents and following up with different offices. Admissions staff review documents, finance staff confirm payment, and green cards are issued after clearance. This process can delay students and make it difficult to track the current status of each submission."),
    ("h2", "4.2 Proposed System"),
    ("p", "The proposed system is a web-based platform where students submit required documents and payment information online. The system stores submissions, performs machine-assisted document checks, routes records to admissions, forwards approved cases to finance, and allows admissions to issue green cards after finance clearance."),
    ("h2", "4.3 System Users"),
    ("list", "Student: submits documents, tracks status, receives notifications, and downloads the green card."),
    ("list", "Admissions Officer: reviews documents, automated verification results, and issues green cards."),
    ("list", "Finance Officer: reviews payment evidence and records finance clearance decisions."),
    ("list", "Administrator: manages users, reports, settings, backups, audit logs, and system health."),
    ("list", "Verifier: confirms green card authenticity using card details or QR verification."),
    ("h2", "4.4 Functional Requirements"),
    ("list", "The system shall allow students to register, log in, complete profiles, and submit documents."),
    ("list", "The system shall validate uploaded files and store them in controlled directories."),
    ("list", "The system shall call the Python verification engine and store confidence scores and risk flags."),
    ("list", "The system shall allow admissions and finance staff to make controlled workflow decisions."),
    ("list", "The system shall generate digital green cards with registration numbers and QR verification."),
    ("h2", "4.5 System Flow"),
    ("p", "The system flow begins when a student logs in and uploads an admission letter, academic document, identification document, passport photo, bank slip, and optionally a bursary award letter. PHP validates the submission and stores the files. The Python engine analyzes documents and returns JSON results containing status, confidence score, extracted fields, missing fields, and risk flags. Admissions reviews the submission and forwards valid cases to finance. Finance verifies payment and sends cleared cases back to admissions. Admissions issues the green card, and the student downloads it. The card can later be verified through the public verification page."),
    ("h2", "4.6 Database Design"),
    ("p", "The database includes tables for users, student profiles, document submissions, document uploads, admissions verifications, finance clearances, green cards, notifications, audit logs, intakes, backups, and settings. The green card table stores card number, registration number, student details, issue date, expiry date, QR data, PDF path, and issuing officer."),
    ("page", ""),
    ("h1", "CHAPTER FIVE: SYSTEM IMPLEMENTATION, TESTING AND EVALUATION"),
    ("h2", "5.1 Implementation"),
    ("p", "The system was implemented as a modular PHP and MySQL web application. The student module handles profile data and document submission. The admissions module handles document review and green card issuance. The finance module handles payment clearance. The administrator module manages users, reports, audit logs, backups, intakes, and system settings."),
    ("h2", "5.2 Python Verification Integration"),
    ("p", "The Python verification component is called from PHP after file upload. It verifies document type, extracts text or image properties, checks required fields, identifies missing or suspicious content, calculates confidence scores, and returns structured JSON. The result is stored with each uploaded document for admissions review."),
    ("h2", "5.3 Registration Number and Green Card Issuance"),
    ("p", "The green card issuance process generates registration numbers in the format YEAR-MONTH-####. For example, the first student in the August 2026 intake receives 2026-08-1001, followed by 2026-08-1002 and 2026-08-1003. Each new intake starts again from 1001. The system uses the existing PDO database connection to check the last issued number from the green_cards table and increments the number safely during issuance."),
    ("h2", "5.4 Testing"),
    ("p", "Testing confirmed that students can submit required documents, award letter uploads can automatically mark bursary status as Yes, admissions can review documents, finance can verify payments, and green cards can be issued with the correct registration number format. PHP syntax checks were also used to ensure that modified files remained valid."),
    ("h2", "5.5 Evaluation"),
    ("p", "The system improves the manual process by increasing speed, consistency, traceability, and accountability. Students can track progress, staff can view organized queues, and administrators can monitor system activity. The Python verification layer improves review support by identifying missing fields and suspicious documents before final staff decisions."),
    ("page", ""),
    ("h1", "CHAPTER SIX: CONCLUSION AND RECOMMENDATION"),
    ("h2", "6.1 Conclusion"),
    ("p", "The study successfully designed and implemented an automated tuition verification and green card issuance system for Kampala International University. The system addresses delays, weak tracking, repeated paperwork, and limited visibility in the manual process. It provides a structured workflow from student submission to admissions review, finance clearance, and green card issuance."),
    ("p", "The integration of Python-based document verification improves the system by supporting OCR, document classification, field extraction, confidence scoring, and risk-flagging. Although staff still make final decisions, the system provides useful automated evidence to support faster and more consistent review."),
    ("h2", "6.2 Recommendations"),
    ("list", "KIU should deploy the system on a secure institutional server with HTTPS and regular backups."),
    ("list", "Staff should be trained to use the workflow and interpret automated verification results."),
    ("list", "The system should be integrated with official finance and student information systems."),
    ("list", "The Python verification engine should be improved using more document samples and stronger OCR preprocessing."),
    ("list", "Regular maintenance should be performed to protect student data and preserve system reliability."),
    ("h2", "6.3 Areas for Further Research"),
    ("p", "Further research may investigate direct integration with banking systems, national identity verification, examination-board validation, and machine learning models trained on larger document datasets. Future studies may also measure system performance after full institutional deployment."),
    ("page", ""),
    ("h1", "7.0 REFERENCES"),
    ("p", "Ameen, N., Tarhini, A., Reppel, A., & Anand, A. (2023). Customer experiences in the age of artificial intelligence in financial services: A systematic literature review. Journal of Business Research."),
    ("p", "Oliveira, M. M. S., Lopes, I., & Sousa, S. (2023). Automation and digitalization in higher education institutions: A systematic literature review. Education Sciences, 13(2)."),
    ("p", "Venkatesh, V., Thong, J. Y. L., & Xu, X. (2022). Unified theory of acceptance and use of technology: A synthesis and the road ahead. Journal of the Association for Information Systems."),
    ("p", "Kampala International University Finance Office. (2023). Tuition verification process audit report. Internal document."),
    ("page", ""),
    ("h1", "7.0 APPENDICES"),
    ("h2", "Appendix I: Proposed Work Plan"),
    ("p", "Requirements gathering, system design, implementation, testing, documentation, and final submission."),
    ("h2", "Appendix II: Proposed Budget"),
    ("p", "The budget includes development tools, internet access, printing, binding, transport, and contingency costs."),
    ("h2", "Appendix III: System Screens"),
    ("p", "Student dashboard, document submission form, admissions review page, finance review page, green card issuance interface, and public verification page."),
]


def p_xml(text: str, style: str | None = None, page_break: bool = False, bullet: bool = False) -> str:
    ppr = ""
    if style:
        ppr += f'<w:pStyle w:val="{style}"/>'
    if bullet:
        ppr += '<w:ind w:left="720" w:hanging="360"/>'
    if ppr:
        ppr = f"<w:pPr>{ppr}</w:pPr>"
    if page_break:
        return '<w:p><w:r><w:br w:type="page"/></w:r></w:p>'
    prefix = "• " if bullet else ""
    safe = html.escape(prefix + text)
    return f'<w:p>{ppr}<w:r><w:t xml:space="preserve">{safe}</w:t></w:r></w:p>'


def build_document_xml() -> str:
    body = []
    for kind, text in SECTIONS:
        if kind == "page":
            body.append(p_xml("", page_break=True))
        elif kind == "title":
            body.append(p_xml(text, "Title"))
        elif kind == "center":
            body.append(
                '<w:p><w:pPr><w:jc w:val="center"/></w:pPr>'
                f'<w:r><w:t xml:space="preserve">{html.escape(text)}</w:t></w:r></w:p>'
            )
        elif kind == "h1":
            body.append(p_xml(text, "Heading1"))
        elif kind == "h2":
            body.append(p_xml(text, "Heading2"))
        elif kind == "h3":
            body.append(p_xml(text, "Heading3"))
        elif kind == "list":
            body.append(p_xml(text, bullet=True))
        else:
            body.append(p_xml(text))

    section = (
        '<w:sectPr>'
        '<w:pgSz w:w="11906" w:h="16838"/>'
        '<w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="720" w:footer="720" w:gutter="0"/>'
        '</w:sectPr>'
    )
    return (
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
        '<w:body>' + "".join(body) + section + '</w:body></w:document>'
    )


def build_styles_xml() -> str:
    return '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:style w:type="paragraph" w:default="1" w:styleId="Normal">
    <w:name w:val="Normal"/>
    <w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/><w:sz w:val="24"/></w:rPr>
    <w:pPr><w:spacing w:after="160" w:line="360" w:lineRule="auto"/><w:jc w:val="both"/></w:pPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Title">
    <w:name w:val="Title"/>
    <w:basedOn w:val="Normal"/>
    <w:pPr><w:jc w:val="center"/><w:spacing w:after="360"/></w:pPr>
    <w:rPr><w:b/><w:sz w:val="28"/></w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Heading1">
    <w:name w:val="heading 1"/>
    <w:basedOn w:val="Normal"/>
    <w:pPr><w:spacing w:before="240" w:after="180"/></w:pPr>
    <w:rPr><w:b/><w:sz w:val="28"/></w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Heading2">
    <w:name w:val="heading 2"/>
    <w:basedOn w:val="Normal"/>
    <w:pPr><w:spacing w:before="180" w:after="120"/></w:pPr>
    <w:rPr><w:b/><w:sz w:val="26"/></w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Heading3">
    <w:name w:val="heading 3"/>
    <w:basedOn w:val="Normal"/>
    <w:pPr><w:spacing w:before="120" w:after="100"/></w:pPr>
    <w:rPr><w:b/><w:sz w:val="24"/></w:rPr>
  </w:style>
</w:styles>'''


def main() -> None:
    OUT.parent.mkdir(parents=True, exist_ok=True)
    content_types = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
</Types>'''
    rels = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>'''
    doc_rels = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>'''

    with zipfile.ZipFile(OUT, "w", zipfile.ZIP_DEFLATED) as z:
        z.writestr("[Content_Types].xml", content_types)
        z.writestr("_rels/.rels", rels)
        z.writestr("word/_rels/document.xml.rels", doc_rels)
        z.writestr("word/document.xml", build_document_xml())
        z.writestr("word/styles.xml", build_styles_xml())

    print(OUT.resolve())


if __name__ == "__main__":
    main()
