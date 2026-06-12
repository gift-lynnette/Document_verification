from __future__ import annotations

import datetime
import shutil
import zipfile
import xml.etree.ElementTree as ET
from pathlib import Path


SRC = Path(r"C:\Users\giftl\Desktop\currentreprt.docx")
OUT = Path(r"C:\xampp\htdocs\research\reports\current_report_ml_integrated_chapters_3_to_6.docx")

W = "http://schemas.openxmlformats.org/wordprocessingml/2006/main"
XML = "http://www.w3.org/XML/1998/namespace"
ET.register_namespace("w", W)


def qn(tag: str) -> str:
    return f"{{{W}}}{tag}"


CONTENT = [
    ("h", "CHAPTER THREE"),
    ("h", "METHODOLOGY"),
    ("p", "This chapter presents the methodology used in the study and explains how the automated tuition verification and green card issuance system was developed after integrating the Python-based machine-assisted document verification component. The chapter maintains the original research context while updating the development approach to reflect the current system architecture, data collection methods, system design activities, testing procedures, and ethical considerations."),
    ("h", "3.1 Research Design"),
    ("p", "The study adopted a mixed-methods research design supported by a case study approach. The mixed-methods design was suitable because the research required both quantitative and qualitative information about the current manual tuition verification process at Kampala International University. Quantitative data helped measure delays, user satisfaction, document submission challenges, and expected system benefits, while qualitative information from staff and students helped explain workflow problems and institutional requirements."),
    ("p", "A case study approach was appropriate because the system was designed specifically for the KIU tuition verification and green card issuance environment. The study focused on the actual actors involved in the process, namely students, admissions staff, finance staff, and administrators. This allowed the developed system to respond to the real institutional flow rather than a general student management model."),
    ("h", "3.2 Target Population"),
    ("p", "The target population consisted of students, admissions office personnel, finance office personnel, and administrative staff involved in student registration, document verification, payment confirmation, and green card issuance. Students were included because they experience the submission and follow-up process directly. Admissions and finance staff were included because they perform verification and clearance activities. Administrative users were included because they supervise reports, user management, audit logs, backup operations, and system health."),
    ("h", "3.3 Sample Size and Sampling Technique"),
    ("p", "The study used a sample of respondents drawn from the main user groups of the system. Students formed the largest group because they are the primary users of the submission portal. Admissions, finance, and administration staff were selected purposively because they possess direct knowledge of the current verification process and the requirements for a digital workflow. Purposive sampling was used for staff respondents, while students were selected using simple random sampling where possible."),
    ("h", "3.4 Data Collection Methods"),
    ("p", "Data was collected using questionnaires, interviews, observation, and document review. Questionnaires captured the experiences of students regarding the manual process, including delays, repeated office visits, lack of status visibility, and challenges in document submission. Interviews with admissions, finance, and administrative staff provided detailed information about approval stages, payment confirmation, record keeping, and green card issuance."),
    ("p", "Observation was used to understand the practical movement of documents between offices and to identify where automation could reduce delays. Document review helped identify the types of records required by the system, including admission letters, academic certificates, identification documents, passport photos, bank slips, bursary award letters, green cards, and audit records."),
    ("h", "3.5 Data Analysis Methods"),
    ("p", "Quantitative data from questionnaires was summarized using frequencies, percentages, and comparative interpretation. The analysis focused on delays in manual verification, difficulty in tracking status, repeated document handling, user satisfaction, and expected benefits of automation. Qualitative data from interviews and observation was analyzed thematically by grouping responses into common themes such as workflow delays, document authenticity concerns, finance clearance challenges, communication gaps, and reporting needs."),
    ("p", "The findings from data analysis guided the design of the system modules and the integration of the Python verification engine. The need to reduce document-checking delays and improve consistency justified the inclusion of automated OCR-based extraction, document classification, confidence scoring, and risk-flag generation before staff review."),
    ("h", "3.6 System Development Methodology"),
    ("p", "The system was developed using Agile methodology. Agile was suitable because the project required incremental development, frequent testing, and continuous refinement of requirements. The system was built in modules, beginning with authentication and student submission, followed by admissions verification, finance clearance, green card generation, reporting, notification, audit logging, and finally the Python-based document verification layer."),
    ("p", "The Agile process was organized into short development iterations. In each iteration, a specific part of the system was designed, implemented, tested, and improved. This allowed the system to evolve from a manual record-submission concept into a more complete automated workflow with machine-assisted document verification."),
    ("h", "3.7 System Requirements Analysis"),
    ("p", "Functional requirements included user registration and login, student profile management, document upload, automated document verification, admissions review, finance clearance, green card generation, QR-code verification, notifications, report generation, audit logging, backup management, and administration of users and intakes. Non-functional requirements included security, data integrity, usability, reliability, maintainability, traceability, and acceptable performance on a local XAMPP-based deployment environment."),
    ("p", "The machine learning integration introduced additional requirements. Uploaded documents had to be saved in controlled directories, hashed for duplicate detection and caching, passed to a local Python verification script, processed using OCR and rule-based classification, and returned to PHP as strict JSON. The system also had to store verification status, confidence score, extracted fields, missing fields, risk flags, OCR hash, engine version, and verification time."),
    ("h", "3.8 Machine Learning and Python API Integration Method"),
    ("p", "The system integrates a local Python verification API through a PHP bridge. When a student uploads a document, the PHP application validates the file and calls the Python verifier using the expected document type. The Python layer extracts text using OCR where necessary, classifies the document, extracts key fields, compares the content with reference standards, calculates a confidence score, and returns a structured JSON result to PHP."),
    ("p", "The verification engine supports admission letters, academic certificates, national ID or passport documents, former school IDs, passport photos, bursary award letters, and bank slips. The engine uses keyword matching, field extraction patterns, image dimension checks, reference similarity scoring, and risk-flag rules. Although final institutional decisions still remain with authorized staff, the Python layer provides machine-assisted evidence that makes review faster, more consistent, and more transparent."),
    ("p", "The status thresholds are defined as APPROVED for documents scoring at least 75 with required fields present, REVIEW for documents scoring at least 50 or where the engine cannot make a reliable decision, and REJECTED for documents with low confidence, empty OCR output, invalid files, or critical missing information. This approach ensures that automation supports staff decisions without removing human oversight in sensitive admissions and finance cases."),
    ("h", "3.9 Development Tools and Technologies"),
    ("p", "The system was implemented using PHP for server-side logic, MySQL for database storage, HTML, CSS, and JavaScript for the user interface, and Apache through XAMPP for local hosting. The Python layer uses local scripts for OCR extraction, document classification, validation rules, and verification scoring. Supporting tools include Tesseract OCR and Poppler for improved extraction from images and PDF documents."),
    ("p", "The project structure separates configuration files, shared PHP classes, application modules, API endpoints, uploads, reports, backups, and Python verification scripts. This separation improves maintainability and makes the document verification component easier to test and update independently from the main web application."),
    ("h", "3.10 Testing and Validation Approach"),
    ("p", "Testing was conducted at module level and workflow level. Module-level testing covered login, registration, student submission, document upload validation, admissions review, finance review, green card generation, QR verification, notifications, reports, backups, and admin functions. Workflow testing followed a student submission from upload to automated verification, admissions action, finance clearance, and green card download."),
    ("p", "The Python verification component was tested directly by running the verifier against uploaded files and indirectly through the PHP submission process. The expected output was strict JSON containing document type, status, confidence score, extracted fields, missing fields, risk flags, engine version, and OCR preview or hash where applicable. Integration testing confirmed that PHP could call the Python script, decode the response, store the result in the database, and display the automated result to admissions staff."),
    ("h", "3.11 Ethical and Security Considerations"),
    ("p", "The system handles sensitive student information; therefore, access control, session management, input validation, CSRF protection, audit logging, and controlled file storage were considered important. Only authorized users should access student submissions, verification results, finance decisions, administrative reports, and generated green cards. The system records significant actions in audit logs to support accountability."),
    ("p", "The machine-assisted verification component was designed to support staff decision-making rather than replace institutional judgement. This is important because OCR quality depends on scan clarity, lighting, file type, and document condition. Borderline or suspicious cases are routed for review so that students are not unfairly rejected by an automated score alone."),
    ("h", "CHAPTER FOUR: SYSTEM ANALYSIS AND DESIGN"),
    ("h", "4.0 Introduction"),
    ("p", "This chapter presents the analysis and design of the automated tuition verification and digital green card issuance system after integration of the Python-based machine-assisted document verification component. It explains the existing problem, the proposed system, system users, requirements, architecture, database design, and the complete flow of the system."),
    ("h", "4.1 Analysis of the Existing Manual System"),
    ("p", "The existing manual process required students to submit payment and admission-related documents physically or through uncoordinated channels. Admissions and finance staff then reviewed documents manually, confirmed payments, communicated issues to students, and prepared green cards. This process was time-consuming, difficult to track, and vulnerable to repeated submissions, lost documents, inconsistent verification, and delays during peak registration periods."),
    ("p", "The manual process also provided limited visibility to students. A student often had to visit offices repeatedly to know whether documents had been received, reviewed, rejected, or approved. Staff also lacked a central workflow history showing who handled a submission, what decision was made, and why a document was returned for correction."),
    ("h", "4.2 Proposed System Overview"),
    ("p", "The proposed system is a web-based automated tuition verification and green card issuance platform for KIU. It provides role-based access for students, admissions staff, finance staff, and administrators. Students upload required documents online, the system performs automated machine-assisted checks using the Python verification engine, admissions staff review the results, finance staff confirm payment, and approved students receive downloadable digital green cards with QR-code verification."),
    ("p", "The system is not only a storage platform. It embeds the institutional workflow into the application by controlling status transitions from student submission to admissions review, finance clearance, pending green card, and issued green card. Each stage records decisions, reasons, timestamps, and responsible users."),
    ("h", "4.3 System Actors"),
    ("p", "The main actors are the Student, Admissions Officer, Finance Officer, Administrator, and Public Verifier. The Student registers, logs in, completes profile information, uploads documents, receives notifications, tracks status, and downloads the green card. The Admissions Officer reviews student documents and automated verification results, approves valid submissions, rejects invalid documents, or requests resubmission. The Finance Officer reviews payment information and clears, rejects, or flags payments. The Administrator manages users, intakes, reports, audit logs, backups, and system health. The Public Verifier uses the green-card verification page to confirm the validity of a card."),
    ("h", "4.4 Functional Requirements"),
    ("p", "The system should allow students to create accounts, authenticate securely, enter academic and personal information, upload required documents, and monitor submission status. It should validate uploaded files, call the Python verification engine, store automated verification results, and present those results to admissions staff. It should allow admissions staff to review documents, issue decisions, and forward valid submissions to finance."),
    ("p", "The system should allow finance staff to verify payment evidence, record clearance decisions, identify partial payments or deferrals, and move cleared submissions toward green-card generation. It should generate digital green cards, create QR codes, support public verification, send notifications, export reports, maintain audit logs, and support backups."),
    ("h", "4.5 Non-Functional Requirements"),
    ("p", "The system should be secure, reliable, usable, maintainable, and traceable. Security is required because the platform stores personal, academic, and payment-related information. Reliability is required because students and staff depend on the platform during registration periods. Usability is important because students and staff may have different levels of technical skill. Maintainability is supported through modular PHP files and a separate Python verification layer. Traceability is achieved through workflow histories, audit logs, verification timestamps, and stored decision reasons."),
    ("h", "4.6 System Architecture"),
    ("p", "The system uses a modular client-server architecture. The presentation layer consists of web pages accessed through a browser. The application layer is implemented in PHP and contains authentication, validation, workflow processing, notification, green-card generation, reporting, and administration logic. The data layer uses MySQL to store users, profiles, submissions, uploaded documents, verification decisions, finance clearance, notifications, audit logs, green cards, and settings."),
    ("p", "The machine-assisted verification layer is implemented separately in Python. PHP communicates with this layer through the DocumentVerificationEngine bridge, passing the uploaded file path and expected document type. Python processes the file and returns a JSON response. This design keeps the web application stable while allowing the document intelligence component to be improved independently."),
    ("h", "4.7 Proper Flow of the System"),
    ("p", "The complete system flow is as follows: 1. A student registers and logs in. 2. The student completes profile, programme, intake, and payment information. 3. The student uploads the required documents, including admission letter, academic certificate, identification document, passport photo, bank slip, and optional bursary award letter. 4. PHP validates the form, checks file requirements, stores files in controlled upload folders, and records the submission. 5. PHP calls the Python verification engine for each uploaded document using the expected document type. 6. Python extracts readable text or image properties, classifies the document, extracts fields, scores the document, identifies missing fields and risk flags, and returns JSON. 7. PHP stores the verification status, confidence score, extracted data, risk flags, OCR hash, engine version, and timestamp in the database. 8. Admissions staff review the submission together with the automated verification results. 9. If documents are valid, the submission moves to finance; if not, it is rejected or returned for resubmission. 10. Finance staff review payment evidence and approve, reject, or flag the payment. 11. When finance clearance is granted, the system generates a green card and QR code. 12. The student downloads the green card. 13. Any authorized person can verify the green card through the verification page. 14. Administrators monitor reports, audit logs, backups, users, intakes, and system health."),
    ("h", "4.8 Data Flow Description"),
    ("p", "At input level, the student provides personal details, academic details, payment information, and digital documents. At processing level, the PHP application validates inputs, stores files, creates submission records, and triggers the Python verification process. The Python engine processes each document and returns evidence-based verification results. Admissions and finance staff then make controlled workflow decisions. At output level, the system provides status updates, staff dashboards, notifications, reports, verification results, and downloadable green cards."),
    ("h", "4.9 Database Design"),
    ("p", "The database supports the central workflow by storing users, student profiles, document submissions, document uploads, admissions verification records, finance verification records, generated green cards, QR codes, notifications, audit logs, settings, and backup information. The document upload records include verification-related fields such as classification result, ownership verification status, manual review flag, file hash, OCR extracted text, confidence score, verification status, extracted data, risk flags, verification document type, engine version, verification error, and verification timestamp."),
    ("p", "This database design ensures that automated verification evidence remains linked to the original student submission. It also supports accountability because staff decisions can be reviewed together with the machine-generated indicators that were available at the time of review."),
    ("h", "4.10 Interface Design"),
    ("p", "The interface design follows the needs of each user role. Students use a submission form, document upload controls, status pages, notifications, and green-card download pages. Admissions staff use a dashboard and document verification screens showing uploaded files, extracted fields, confidence scores, missing fields, and risk flags. Finance staff use payment review screens and clearance controls. Administrators use dashboards for users, reports, audit logs, backups, intakes, and system health."),
    ("h", "CHAPTER FIVE: SYSTEM IMPLEMENTATION, TESTING AND EVALUATION"),
    ("h", "5.0 Introduction"),
    ("p", "This chapter presents the implementation, testing, and evaluation of the KIU automated tuition verification and green card issuance system after integration of the Python-based document verification component. It explains how the system was built, how the modules interact, how the Python API was connected, and how the system was tested."),
    ("h", "5.1 Implementation Environment"),
    ("p", "The system was implemented in a local web development environment using XAMPP, Apache, PHP, MySQL, HTML, CSS, JavaScript, and Python. PHP handles the main application logic and user workflows, while MySQL stores structured records. Python handles OCR-supported verification, classification, field extraction, similarity checks, risk-flagging, and scoring. Tesseract OCR and Poppler support extraction from image and PDF documents where available."),
    ("h", "5.2 Implemented Modules"),
    ("p", "The implemented modules include the student module, admissions module, finance module, administration module, notification service, audit log service, green-card generation service, QR verification page, reporting service, backup service, and Python document verification layer. Shared PHP classes manage authentication, sessions, validation, file uploads, encryption, audit logs, notifications, and document verification bridging."),
    ("p", "The student module allows registration, login, profile management, document submission, notification viewing, status tracking, and green-card download. The admissions module supports document review and verification decisions. The finance module supports payment review and clearance. The administration module supports users, intakes, reports, system health, audit logs, backups, and general control of the platform."),
    ("h", "5.3 Python-Based Machine-Assisted Verification Implementation"),
    ("p", "The Python verification component is implemented through the verify.py script and supporting OCR and validation files. PHP invokes the verifier through the DocumentVerificationEngine class. The bridge resolves the uploaded file path, calculates or receives a file hash, checks whether a cached verification result already exists, executes the Python script, reads the JSON output, validates the response, and normalizes the result for storage and display."),
    ("p", "The Python verifier checks whether the file exists, whether the file is empty, whether it exceeds the allowed size, and whether the document type is supported. For document files, it extracts text through OCR or PDF text extraction. It classifies the document using expected type and document keywords. It extracts fields such as name, institution, date, admission number, programme, bank name, payment reference, payment amount, ID number, and official markers. It also detects risk flags such as missing critical keywords, very little readable text, sample or template wording, repeated number patterns, wrong document type indicators, and unreliable bank-slip crops."),
    ("p", "For passport photos, the engine checks whether the file is an image and reads dimensions and aspect ratio. For text-based documents, it combines field scores, keyword hits, reference similarity, missing required fields, and risk flags to calculate a final confidence score. The final result is returned as APPROVED, REVIEW, or REJECTED."),
    ("h", "5.4 Integration With the Main Workflow"),
    ("p", "The integration occurs during student document submission. After the PHP validation rules confirm that required fields and files are present, the uploaded files are stored and then passed to the Python verifier. The JSON result is stored in the document upload record. Admissions staff can then review the original uploaded document together with the automated result. This improves the quality of review because staff can see the confidence score, extracted fields, missing fields, and risk flags before making a decision."),
    ("p", "The workflow remains controlled by human officers. A high automated score supports faster approval, a medium score routes the document for review, and a low score warns staff about possible rejection. Finance clearance and final green-card issuance still depend on authorized staff decisions, which preserves institutional accountability."),
    ("h", "5.5 Testing Methodology"),
    ("p", "Testing was carried out using functional testing, integration testing, validation testing, security-related testing, and workflow testing. Functional testing confirmed that each module performed its expected task. Integration testing confirmed that student submission, Python verification, database storage, admissions review, finance clearance, and green-card generation worked together. Validation testing checked required fields, file upload rules, amount fields, programme selections, intake data, and date of birth rules."),
    ("p", "Security-related testing considered login control, role-based access, CSRF checks, audit logging, controlled upload paths, file hash storage, and safe execution of the Python verifier. Workflow testing followed the full lifecycle of a submission from student upload to automated verification, admissions decision, finance decision, green-card generation, QR-code verification, and report visibility."),
    ("h", "5.6 Testing Results"),
    ("p", "Testing showed that the major system functions are present and connected. Students can submit required documents and payment information. Uploaded documents can be processed by the Python verification layer. Verification results are returned in JSON and stored with confidence scores, extracted data, risk flags, and timestamps. Admissions staff can review submissions using both uploaded documents and automated evidence. Finance staff can perform clearance decisions. Green cards and QR verification support the final output of the process."),
    ("p", "The Python integration improves the system by reducing the amount of manual checking required at the first review stage. It highlights missing fields, suspicious document indicators, and low-quality OCR output. However, the results also show that OCR accuracy depends on scan quality and availability of tools such as Tesseract and Poppler. For that reason, the REVIEW status remains important for borderline cases."),
    ("h", "5.7 Evaluation of the Implemented System"),
    ("p", "The implemented system addresses the main weaknesses of the manual process by improving speed, visibility, record traceability, and consistency of document review. Students benefit from online submission and status tracking. Admissions and finance staff benefit from organized dashboards, automated indicators, and clear workflow queues. Administrators benefit from reports, audit logs, backups, system health monitoring, and centralized management."),
    ("p", "The machine-assisted verification component adds value because it provides an early assessment of uploaded documents before staff review. It does not eliminate the need for human decision-making, but it reduces repetitive checking and creates a more evidence-based review process. The system is therefore more suitable for institutional use than a purely manual workflow."),
    ("h", "5.8 Challenges Encountered During Implementation"),
    ("p", "Several challenges were encountered. Document images and PDF files varied in quality, making OCR output inconsistent in some cases. Bank slips were especially difficult because of different bank formats, handwritten figures, stamps, and cropped uploads. Another challenge was ensuring that PHP could call Python reliably and receive clean JSON output. These challenges were addressed by using expected document types, file-size checks, fallback review statuses, JSON normalization, risk flags, and clear thresholds."),
    ("h", "CHAPTER SIX: DISCUSSION, CONCLUSION AND RECOMMENDATIONS"),
    ("h", "6.0 Introduction"),
    ("p", "This chapter discusses the findings from the design and implementation of the KIU automated tuition verification and green card issuance system after integration of the Python-based machine-assisted verification component. It relates the developed system to the research objectives and presents conclusions, recommendations, limitations, and areas for further research."),
    ("h", "6.1 Discussion"),
    ("h", "6.1.1 Examination of the Existing Manual Verification Process"),
    ("p", "The study established that the manual tuition verification and green card issuance process was affected by delays, weak tracking, repeated office visits, document handling challenges, and limited transparency. These findings supported the need for an automated system that could centralize submissions and provide a clear workflow for students, admissions staff, finance staff, and administrators."),
    ("h", "6.1.2 Design and Development of the Automated System"),
    ("p", "The developed system responds to the identified challenges through a modular web-based workflow. Students submit records online, admissions staff verify academic and identity documents, finance staff clear payments, and the system generates digital green cards. The design supports role-based access, status tracking, notifications, audit logging, reporting, backup management, and public QR verification."),
    ("h", "6.1.3 Contribution of the Python API and Machine-Assisted Verification"),
    ("p", "The integration of the Python verification layer strengthens the system by adding automated document intelligence. The engine extracts text, classifies documents, identifies important fields, checks document-specific rules, calculates confidence scores, and produces risk flags. This improves the review process because staff receive structured evidence instead of relying only on visual inspection of uploaded files."),
    ("p", "The Python component is especially useful for detecting incomplete or suspicious submissions early. For example, it can identify missing admission evidence, missing bank slip reference numbers, low-readable OCR output, wrong document type indicators, invalid passport photo dimensions, and documents that appear to be samples or templates. This supports faster routing of valid documents and better attention to documents requiring manual review."),
    ("h", "6.1.4 Evaluation of System Performance Compared With the Manual Process"),
    ("p", "Compared with the manual process, the implemented system provides stronger visibility, better record control, improved traceability, and a more consistent verification path. Students can submit documents digitally and track progress. Staff can manage submissions through role-based dashboards. Administrators can monitor activity using reports and audit logs. The automated verification layer further improves performance by reducing repetitive document checking and standardizing early review indicators."),
    ("h", "6.2 Conclusion"),
    ("p", "The study concludes that the previous manual process was not sufficient for efficient tuition verification and green card issuance at KIU. It was slow, difficult to monitor, and vulnerable to inconsistent document handling. The implemented automated system provides a practical solution by centralizing student submissions, structuring admissions and finance workflows, and generating digital green cards with QR verification."),
    ("p", "The integration of the Python-based machine-assisted verification component improves the system beyond ordinary digitization. It provides document classification, OCR-supported field extraction, scoring, risk-flagging, and review support. This makes the system more effective for handling admission letters, certificates, identification documents, passport photos, bank slips, and bursary award letters. The system therefore meets the major objectives of designing and implementing an automated tuition verification and green card issuance platform for KIU."),
    ("h", "6.3 Recommendations"),
    ("p", "KIU should deploy the system on a secure institutional server with HTTPS, controlled user access, regular backups, and reliable database maintenance. Staff should be trained on the full workflow, including how to interpret automated verification statuses, confidence scores, missing fields, and risk flags. Students should also receive clear guidance on uploading readable documents to improve OCR accuracy."),
    ("p", "The system should be integrated with official university payment and student information systems so that finance clearance can be confirmed more directly. The Python verification engine should be expanded with more reference samples, improved OCR preprocessing, better bank-slip format handling, duplicate document detection, and stronger anomaly detection. Regular monitoring should be carried out to ensure that automated scores support fair decisions and do not replace necessary human judgement."),
    ("h", "6.4 Limitations of the Study"),
    ("p", "The system was developed and tested in a local environment rather than through a full institutional production deployment. OCR performance may vary depending on document quality, scan resolution, lighting, file type, and availability of supporting tools. The current Python verification approach is machine-assisted and rule-guided; it supports review but does not prove document authenticity against external banks, examination bodies, or government identity systems."),
    ("p", "Some decisions still require manual judgement, especially where payments are partial, documents are unclear, or submissions contain exceptional cases. These limitations are acceptable for the current scope because the goal of the system is to improve the workflow and support staff decisions, not to remove institutional verification responsibility entirely."),
    ("h", "6.5 Areas for Further Research"),
    ("p", "Further research may focus on improving the machine learning component using larger labelled datasets of genuine and rejected documents. Future work may also investigate direct integration with bank payment systems, national identity verification services, examination-board verification systems, and the university student information system. Another area for further research is measuring the system after full deployment to compare actual processing time, user satisfaction, error reduction, and operational cost against the manual process."),
    ("h", "6.6 Final Remarks"),
    ("p", "The KIU automated tuition verification and green card issuance system demonstrates how a university administrative process can be improved through a combination of web-based workflow automation and Python-based machine-assisted document verification. The system preserves institutional control while improving speed, transparency, accountability, and readiness for future integration with official data sources."),
]


def make_p(text: str, style: str | None = None) -> ET.Element:
    p = ET.Element(qn("p"))
    if style:
        p_pr = ET.SubElement(p, qn("pPr"))
        p_style = ET.SubElement(p_pr, qn("pStyle"))
        p_style.set(qn("val"), style)
    r = ET.SubElement(p, qn("r"))
    t = ET.SubElement(r, qn("t"))
    t.set(f"{{{XML}}}space", "preserve")
    t.text = text
    return p


def paragraph_text(p: ET.Element) -> str:
    return "".join(t.text or "" for t in p.findall(".//" + qn("t"))).strip()


def main() -> None:
    OUT.parent.mkdir(parents=True, exist_ok=True)
    shutil.copyfile(SRC, OUT)

    with zipfile.ZipFile(SRC, "r") as zin:
        files = {name: zin.read(name) for name in zin.namelist()}

    root = ET.fromstring(files["word/document.xml"])
    body = root.find(qn("body"))
    if body is None:
        raise RuntimeError("Could not find Word document body")

    children = list(body)
    start_child = None
    end_child = None
    for child_index, child in enumerate(children):
        if child.tag != qn("p"):
            continue
        text = paragraph_text(child)
        if text == "CHAPTER THREE" and start_child is None:
            start_child = child_index
        elif text == "REFERENCES" and start_child is not None:
            end_child = child_index
            break

    if start_child is None or end_child is None:
        raise RuntimeError(f"Could not locate replacement range: {start_child}, {end_child}")

    for child in children[start_child:end_child]:
        body.remove(child)

    new_elements = [
        make_p(text, "Heading1" if kind == "h" else None)
        for kind, text in CONTENT
    ]
    for elem in reversed(new_elements):
        body.insert(start_child, elem)

    files["word/document.xml"] = ET.tostring(root, encoding="utf-8", xml_declaration=True)

    if "docProps/core.xml" in files:
        try:
            core = ET.fromstring(files["docProps/core.xml"])
            modified = core.find("{http://purl.org/dc/terms/}modified")
            if modified is not None:
                modified.text = datetime.datetime.utcnow().replace(microsecond=0).isoformat() + "Z"
            files["docProps/core.xml"] = ET.tostring(core, encoding="utf-8", xml_declaration=True)
        except ET.ParseError:
            pass

    with zipfile.ZipFile(OUT, "w", zipfile.ZIP_DEFLATED) as zout:
        for name, data in files.items():
            zout.writestr(name, data)

    print(OUT)


if __name__ == "__main__":
    main()
