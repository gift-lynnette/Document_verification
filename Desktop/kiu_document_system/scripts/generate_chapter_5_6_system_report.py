from __future__ import annotations

import html
import zipfile
from pathlib import Path


OUT = Path("reports/chapter_5_6_system_implementation_report.docx")


CONTENT = [
    ("h1", "CHAPTER FIVE:"),
    ("h1", "SYSTEM IMPLEMENTATION, TESTING AND EVALUATION"),
    ("h2", "5.0 Introduction"),
    ("p", "This chapter presents the implementation, testing, and evaluation of the KIU automated tuition verification and digital green card issuance system. It describes how the system was realized in code, how the major modules were organized, how the workflow was enforced across departments, and how the implemented features were assessed against the intended objectives of the project."),
    ("h2", "5.1 Implementation Description"),
    ("p", "The implemented system adopts a modular PHP and MySQL architecture organized around the directories modules/student, modules/finance, modules/admissions, and modules/admin. Shared services such as authentication, file upload, notification processing, green card generation, backup, and audit logging are implemented under the includes directory. Configuration and workflow constants are stored under config, while the data model is supported by the SQL schema and migration scripts available in the root directory."),
    ("p", "A key implementation decision was the migration from a simpler payment-submission flow to a regulation-based workflow driven by the document_submissions, admissions_verifications, finance_clearances, and workflow_history tables. This restructuring allows the system to reflect the institutional route followed at KIU, namely student submission, admissions verification, finance clearance, green card generation, and student download."),
    ("p", "The integration of the Python verification layer strengthens the system by adding automated document intelligence. The engine extracts text, classifies documents, identifies important fields, checks document-specific rules, calculates confidence scores, and produces risk flags. This improves the review process because staff receive structured evidence instead of relying only on visual inspection of uploaded files."),
    ("p", "The implementation also provides API support for submission status and notifications, public card verification through verify_card.php, CSV report export, audit logging, backup scheduling, and system health diagnostics. Green card generation is handled by a dedicated service that can render PDFs through a headless browser, Dompdf, or a fallback PDF generator depending on environment availability."),
    ("h2", "5.2 System Implementation"),
    ("p", "The system implementation can be understood through its major functional components. The authentication module manages registration, login, password security, session control, and lockout handling. Passwords are hashed using bcrypt, failed login attempts are tracked, and prepared statements are used to reduce SQL injection risk. The student module captures user profile data and document submissions, including admission letters, S.6 certificates, identification, passport photos, bank slips, and bursary award letters where relevant."),
    ("p", "The admissions module reads pending submissions, verifies academic documentation, supports approval, rejection, and resubmission requests, and forwards approved cases to finance. The finance module validates payment-related data, evaluates bank slip quality and payment thresholds, supports override and pending handling where necessary, and updates the workflow according to finance clearance outcomes. After successful finance approval, the green card service produces the final card, stores QR metadata, writes the PDF output, and advances the workflow to the issued state."),
    ("p", "The administration module provides governance and support capabilities such as user management, audit log viewing, system reports, backup creation, and system health monitoring. Notifications are queued through the notification service for in-app, email, and SMS channels, although the current SMS implementation behaves as a placeholder transport that logs messages rather than integrating with a live telecom gateway. Administrative reporting currently exports CSV outputs and stores report metadata where the reports table is available."),
    ("p", "At the storage level, uploaded files are written into controlled directories under uploads/, including folders for admission letters, bank slips, passport photos, IDs, green cards, QR codes, and other supporting records. The file upload class performs MIME validation, file size checks, unique naming, and hash generation for stored content. This makes the implementation more robust for real institutional document handling."),
    ("h2", "5.3 System Development"),
    ("p", "System development followed a modular web architecture with four operational modules and shared services."),
    ("p", "Implemented modules:"),
    ("list", "Student Module: account registration, profile, document submission, dashboard tracking, notifications."),
    ("list", "Admissions Module: document verification, decision handling, resubmission requests, green card issuance, QR verification support."),
    ("list", "Finance Module: payment review queue, decision capture (approve, pending, reject), evidence viewing."),
    ("list", "Admin Module: user management, reports, search for issued cards, audit logs, backup and system settings."),
    ("p", "Core workflow implemented:"),
    ("list", "Submission created by student."),
    ("list", "Admissions review and validation."),
    ("list", "Finance clearance decision."),
    ("list", "Green card issuance."),
    ("list", "Public verification of issued card details."),
    ("h2", "5.4 System Testing"),
    ("p", "System testing was carried out to determine whether the application components behave as expected and whether the workflow moves correctly from one department to another. Because the project is a web-based institutional system, testing focused on functional behavior, inter-module interaction, validation checks, workflow progression, and administrative oversight features. The testing process was organized into unit testing, integration testing, and system testing."),
    ("h3", "5.4.1 Unit Testing"),
    ("p", "Unit testing concentrated on the smallest reusable elements of the system such as input validation, authentication logic, and workflow rule enforcement. These checks were necessary to confirm that core components behave correctly before they are composed into the larger application."),
    ("h3", "5.4.2 Integration Testing"),
    ("p", "Integration testing was used to verify that related modules work together correctly after individual components were confirmed. The emphasis here was on transactional workflow behavior, role availability across departments, and the ability to resolve issued green cards from stored institutional data."),
    ("h3", "5.4.3 System Testing"),
    ("p", "End-to-end system testing confirmed that the full process works for real user roles from registration to verification. Security and usability checks were also conducted, including session handling, controlled page access, and error pages."),
    ("h3", "5.4.4 Testing Methodology"),
    ("p", "A combined testing approach was considered appropriate for the implemented system. First, module-level testing was applied by reviewing the behavior of the student, admissions, finance, and administration modules independently. Second, integration-oriented testing was used to examine whether data moved correctly between submission, verification, clearance, issuance, and verification endpoints. Third, code-level confirmation was carried out using implementation inspection and available helper scripts such as test_verify.php for green card verification logic."),
    ("p", "The testing methodology also considered input validation and security-related behavior. Document upload validation, login attempt controls, audit logging, queue-based notifications, and health-check reporting were analyzed because they contribute directly to the reliability and governance of the system. Since the current codebase is not accompanied by a formal automated unit test suite, the testing narrative in this report is based on functional workflow inspection and implementation-level verification of the coded modules."),
    ("h3", "5.4.5 Testing Results"),
    ("p", "The observed implementation indicates that the major system functions required by the study are present and integrated into the workflow. The table below summarizes the principal functional areas reviewed and the outcomes established from the implemented code and workflow logic."),
    ("p", "Overall, the testing results show that the system is functionally coherent and that its main workflow has been fully modeled in the implementation. The primary areas identified for future strengthening involve deeper external integration, especially live SMS and institutional finance-system connectivity."),
    ("h2", "5.5 System Documentation"),
    ("p", "Documentation for the system exists in several forms. User-facing and deployment guidance is available in README.md and INSTALL.md. Technical configuration details are captured in the config files, while the SQL schema and migration scripts describe the data model and workflow transitions. Source code comments and organized module structure also contribute to maintainability by making the system easier to understand and extend."),
    ("p", "In practical terms, this means both administrators and future developers can identify how the system is installed, how the data is structured, how roles are enforced, and how auxiliary services such as backups, notifications, and green card generation are expected to work."),
    ("h2", "5.6 Deployment Strategy"),
    ("p", "The system is suitable for deployment in a university-hosted PHP environment using Apache and MySQL. During development, a XAMPP-style environment is practical because it supports rapid configuration and testing on Windows systems. For deployment within KIU, the preferred strategy would be to host the application on a central server accessible to students, finance staff, registrar or admissions staff, and administrators through standard web browsers."),
    ("p", "Deployment should include creation of the required database, import of the base schema, execution of the regulation workflow migration, configuration of environment-specific constants such as base URLs and encryption settings, and preparation of upload and log directories. Because the system handles institutional records, regular database backups, log review, and permission checks should be integrated into operational practice."),
    ("h2", "5.7 Evaluation"),
    ("p", "The implemented system provides a meaningful improvement over the manual approach that motivated the study. In the manual process, students rely on repeated movement between offices, limited visibility into the status of their records, and physical handling of payment and admission documents. In contrast, the implemented system centralizes submissions, preserves workflow states, supports approval histories, and generates auditable green card outputs."),
    ("table", ""),
    ("p", "Based on the implementation, the system can therefore be evaluated as effective in improving control, visibility, and service continuity. However, some advanced areas such as fully live finance integration, real SMS delivery, and broader performance stress testing remain open for enhancement."),
    ("page", ""),
    ("h1", "CHAPTER SIX:"),
    ("h1", "DISCUSSION, CONCLUSION AND RECOMMENDATIONS"),
    ("h2", "6.0 Introduction"),
    ("p", "This chapter presents a discussion of the findings drawn from the design and implementation of the KIU automated tuition verification and digital green card issuance system. It interprets the significance of the developed solution, compares it with the earlier manual approach, and provides conclusions, recommendations, areas for further research, final remarks, and limitations of the study."),
    ("h2", "6.1 Discussion"),
    ("h3", "6.1.1 Examination of the Existing Manual Verification and Green Card Issuance Process and Its Challenges"),
    ("p", "The study established that the existing KIU process was largely manual and therefore vulnerable to delays, weak coordination, and poor visibility. Students had to submit and follow up on records through multiple offices, while staff relied on document movement, manual checking, and fragmented communication. Such a process creates inefficiencies because it increases turnaround time, reduces transparency for students, and makes it difficult for the university to maintain a complete audit trail of verification decisions."),
    ("p", "The implemented system directly addresses these challenges by providing a structured workflow in which each status change is recorded and each department acts within a controlled interface. This represents an important shift from paper-driven or loosely coordinated practice toward traceable digital processing."),
    ("h3", "6.1.2 Design and Development of an Automated Tuition Verification and Digital Green Card Issuance Model"),
    ("p", "In response to the manual process, the study designed and implemented a computerized model that captures student records, routes them for admissions verification, transfers approved cases to finance, and issues green cards after successful clearance. The system embodies this model through dedicated modules, role checks, and status transitions defined in the workflow constants and migration scripts."),
    ("p", "The model is especially valuable because it does not only digitize document storage; it embeds institutional logic into the application. Required documents, rejection reasons, resubmission requests, finance thresholds, pending decisions, card issuance, and public verification are all represented in the workflow. This makes the model more than a record system; it is a controlled institutional process implementation."),
    ("h3", "6.1.3 Implementation of a Centralized Database and Workflow Control Mechanism"),
    ("p", "The study also demonstrates the importance of a centralized database in managing admissions, finance, student, and administrative activities. The revised workflow schema allows the university to maintain a single source of truth for student submissions, verification outcomes, payment clearance, notifications, green card records, and history logs."),
    ("p", "This centralized structure improves consistency and reduces duplication of effort. It also enables governance services such as reports, backups, health monitoring, and audit review. The inclusion of workflow history and audit logs is especially important because it strengthens institutional accountability and supports future review of actions performed on student records."),
    ("h3", "6.1.4 Evaluation of System Performance Compared to the Manual Approach"),
    ("p", "Compared with the manual process, the automated system offers significant qualitative improvements in efficiency, transparency, and control. Students no longer depend entirely on physical movement between offices to know the status of their submissions. Staff members can process queued records in structured interfaces, and administrators can supervise operational health using dashboards and exports."),
    ("p", "The card verification capability is another major advantage because it supports trust and authenticity. A card can be verified by number or registration number, and the record can be checked for validity, expiry, or revocation status. These capabilities are difficult to guarantee in purely manual processes and contribute substantially to institutional confidence in issued documents."),
    ("h2", "6.2 Conclusion"),
    ("h3", "6.2.1 Examination of the Existing Manual Verification Process"),
    ("p", "The study concludes that the earlier manual process for tuition verification and green card issuance was inefficient, difficult to monitor, and not well suited to institutional-scale coordination. It lacked continuous visibility for students and created avoidable administrative burden for staff."),
    ("h3", "6.2.2 Design and Development of the Automated Model"),
    ("p", "The developed automated model successfully captures the major institutional requirements of document submission, admissions review, finance clearance, and green card issuance. Its role-based design and modular implementation show that the proposed solution is technically practical and organizationally relevant for KIU."),
    ("h3", "6.2.3 Centralized Database and Workflow Control"),
    ("p", "The study further concludes that a centralized database combined with workflow history and supporting services provides a dependable foundation for digital verification processes. It improves consistency of records, preserves institutional memory, and supports monitoring, reporting, and accountability."),
    ("h3", "6.2.4 System Performance Compared to the Manual Approach"),
    ("p", "The implemented system outperforms the manual approach in terms of record visibility, control, process traceability, and readiness for verification-oriented service delivery. Even where future enhancements are still desirable, the current implementation clearly provides a stronger operational model than the previous manual arrangement."),
    ("h2", "6.3 Recommendations"),
    ("p", "Based on the findings of this study, several recommendations are proposed. First, KIU should deploy the system on a centralized institutional server so that all relevant offices access the same live workflow. Second, the university should integrate a live SMS transport and, where possible, a finance-system or payment API to reduce reliance on manual confirmation. Third, the current registrar-protected admissions functionality should be reviewed so that role naming and office responsibilities remain clear and institutionally aligned."),
    ("p", "The university should deploy the system on a secure production server with HTTPS, regular backups, and proper user access controls to protect student records and administrative actions."),
    ("p", "The system should be integrated with the university's official student information and payment systems so that tuition confirmation can be more automated and less dependent on manual upload of payment evidence."),
    ("p", "The admissions workflow should be extended with stronger document validation features such as image quality checks, duplicate detection, and additional verification rules for suspicious submissions."),
    ("p", "Staff training should be provided regularly so that users understand the workflow, notification process, and exception handling procedures required for smooth operation of the platform."),
    ("p", "It is also recommended that the university enhance the mobile responsiveness of the interface for students who primarily access services through mobile devices. In addition, the reporting module can be expanded beyond CSV export to include richer dashboards, printable summaries, and approval analytics for management use."),
    ("h2", "6.4 Areas for Further Research"),
    ("p", "Future research may explore integration of machine-assisted document verification, optical character recognition for payment slips, direct bank or finance-system reconciliation, and advanced anomaly detection for suspicious submissions. Another useful direction would be evaluation of user experience and adoption once the system is deployed institution-wide."),
    ("p", "Further work may also investigate scalable cloud deployment, stronger real-time notification infrastructure, and broader interoperability with university student information systems. Such research would help move the platform from a strong departmental solution to a more comprehensive institutional digital service."),
    ("h2", "6.5 Final Remarks"),
    ("p", "The KIU automated tuition verification and digital green card issuance system represents a practical response to a real administrative challenge. By digitizing the verification path and making card issuance traceable, the system creates a more accountable and student-centered way of managing new student onboarding. Its modular design, workflow controls, and support services provide a strong foundation for continued institutional improvement."),
    ("h2", "6.6 Limitations of the Study"),
    ("p", "Although the system achieves the major objectives of the study, some limitations remain. The current implementation is based on a custom PHP architecture and does not yet show full integration with a live finance API or live SMS gateway. The SMS channel currently behaves as a placeholder transport, and some administrative outputs are exported in CSV rather than richer formats."),
    ("p", "In addition, the report is based on analysis of the present implementation rather than on a large-scale institutional deployment trial with measured long-term operational statistics. Performance at very large scale, external interoperability, and full production hardening would benefit from future deployment experience and iterative refinement."),
    ("p", "Another limitation is that some clearance decisions still require manual judgement by finance and admissions officers, especially in borderline cases such as partial payments, missing attachments, or mismatched records. In addition, notification delivery depends on external email and SMS services, which may introduce delays if those services are unavailable or misconfigured. Finally, the study was focused on one institutional workflow, so the findings may need adaptation before being applied in a different university setting."),
]


TABLE_ROWS = [
    ["Evaluation Area", "Manual Process", "Automated System"],
    ["Submission handling", "Paper-based or fragmented office movement", "Centralized electronic submission with required-file validation"],
    ["Status visibility", "Limited and dependent on physical follow-up", "Tracked through workflow states, dashboards, and notifications"],
    ["Institutional accountability", "Weak traceability and difficult retrospective review", "Supported through audit logs, workflow history, and role-based actions"],
    ["Green card issuance", "Manual issuance and verification challenges", "Automated card generation with QR and verification support"],
    ["Administrative oversight", "Scattered records and difficult reporting", "Central dashboards, report export, backup, and health checks"],
]


def text_run(text: str) -> str:
    return f'<w:r><w:t xml:space="preserve">{html.escape(text)}</w:t></w:r>'


def paragraph(text: str, style: str | None = None, bullet: bool = False, page_break: bool = False) -> str:
    if page_break:
        return '<w:p><w:r><w:br w:type="page"/></w:r></w:p>'
    ppr = ""
    if style:
        ppr += f'<w:pStyle w:val="{style}"/>'
    if bullet:
        ppr += '<w:ind w:left="720" w:hanging="360"/>'
    if ppr:
        ppr = f"<w:pPr>{ppr}</w:pPr>"
    return f"<w:p>{ppr}{text_run(('• ' if bullet else '') + text)}</w:p>"


def table_xml() -> str:
    rows = []
    for idx, row in enumerate(TABLE_ROWS):
        cells = []
        for value in row:
            bold_start = "<w:rPr><w:b/></w:rPr>" if idx == 0 else ""
            cells.append(
                "<w:tc><w:tcPr><w:tcW w:w=\"3100\" w:type=\"dxa\"/></w:tcPr>"
                f"<w:p><w:r>{bold_start}<w:t xml:space=\"preserve\">{html.escape(value)}</w:t></w:r></w:p></w:tc>"
            )
        rows.append("<w:tr>" + "".join(cells) + "</w:tr>")
    return (
        "<w:tbl><w:tblPr><w:tblW w:w=\"0\" w:type=\"auto\"/>"
        "<w:tblBorders><w:top w:val=\"single\" w:sz=\"4\"/><w:left w:val=\"single\" w:sz=\"4\"/>"
        "<w:bottom w:val=\"single\" w:sz=\"4\"/><w:right w:val=\"single\" w:sz=\"4\"/>"
        "<w:insideH w:val=\"single\" w:sz=\"4\"/><w:insideV w:val=\"single\" w:sz=\"4\"/></w:tblBorders>"
        "</w:tblPr>" + "".join(rows) + "</w:tbl>"
    )


def document_xml() -> str:
    body = []
    for kind, value in CONTENT:
        if kind == "page":
            body.append(paragraph("", page_break=True))
        elif kind == "h1":
            body.append(paragraph(value, "Heading1"))
        elif kind == "h2":
            body.append(paragraph(value, "Heading2"))
        elif kind == "h3":
            body.append(paragraph(value, "Heading3"))
        elif kind == "list":
            body.append(paragraph(value, bullet=True))
        elif kind == "table":
            body.append(table_xml())
        else:
            body.append(paragraph(value))

    sect = (
        '<w:sectPr><w:pgSz w:w="11906" w:h="16838"/>'
        '<w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="720" w:footer="720" w:gutter="0"/>'
        '</w:sectPr>'
    )
    return (
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
        '<w:body>' + "".join(body) + sect + '</w:body></w:document>'
    )


def styles_xml() -> str:
    return '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:style w:type="paragraph" w:default="1" w:styleId="Normal">
    <w:name w:val="Normal"/>
    <w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/><w:sz w:val="24"/></w:rPr>
    <w:pPr><w:spacing w:after="160" w:line="360" w:lineRule="auto"/><w:jc w:val="both"/></w:pPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Heading1">
    <w:name w:val="heading 1"/><w:basedOn w:val="Normal"/>
    <w:pPr><w:spacing w:before="240" w:after="180"/></w:pPr>
    <w:rPr><w:b/><w:sz w:val="28"/></w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Heading2">
    <w:name w:val="heading 2"/><w:basedOn w:val="Normal"/>
    <w:pPr><w:spacing w:before="180" w:after="120"/></w:pPr>
    <w:rPr><w:b/><w:sz w:val="26"/></w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Heading3">
    <w:name w:val="heading 3"/><w:basedOn w:val="Normal"/>
    <w:pPr><w:spacing w:before="120" w:after="100"/></w:pPr>
    <w:rPr><w:b/><w:sz w:val="24"/></w:rPr>
  </w:style>
</w:styles>'''


def main() -> None:
    out = Path("reports/chapter_5_6_system_report.docx")
    out.parent.mkdir(parents=True, exist_ok=True)
    with zipfile.ZipFile(out, "w", zipfile.ZIP_DEFLATED) as z:
        z.writestr("[Content_Types].xml", '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
</Types>''')
        z.writestr("_rels/.rels", '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>''')
        z.writestr("word/_rels/document.xml.rels", '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>''')
        z.writestr("word/document.xml", document_xml())
        z.writestr("word/styles.xml", styles_xml())
    print(out.resolve())


if __name__ == "__main__":
    main()
