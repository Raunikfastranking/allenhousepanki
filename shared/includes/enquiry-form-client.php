<?php
/**
 * Shared enquiry form payload builder (header popup + admission procedure + landing).
 */
?>
<script>
window.cmsBuildEnquiryPayload = function (fields) {
    fields = fields || {};
    var studentName = (fields.student_name || fields.studentName || "").trim();
    var parentName = (fields.parent_name || fields.parentName || "").trim();
    var grade = (fields.grade || "").trim();
    var session = (fields.session || "").trim();
    var board = fields.board;
    if (board !== undefined && board !== null) {
        board = String(board).trim();
    }
    var source = (fields.source || (typeof sessionStorage !== "undefined" ? sessionStorage.getItem("leadSource") : "") || "website").trim();
    var sourceType = (fields.source_type || fields.sourceType || "organic").trim();
    var message = (fields.message || "").trim();
    if (!message && grade) {
        message = "I want admission details for " + grade + ".";
        if (session) {
            message = "I want admission details for " + grade + " (Session " + session + ").";
        }
    }
    var contactName = (fields.name || "").trim();
    if (!contactName) {
        contactName = parentName || studentName;
    }

    var payload = {
        student_name: studentName,
        studentName: studentName,
        parent_name: parentName,
        parentName: parentName,
        name: contactName,
        email: (fields.email || "").trim(),
        phone: (fields.phone || fields.mobile || "").trim(),
        mobile: (fields.mobile || fields.phone || "").trim(),
        grade: grade,
        city: (fields.city || "").trim(),
        session: session,
        pincode: (fields.pincode || "").trim(),
        enquiry_type: fields.enquiry_type || "admission",
        subject: fields.subject || "Admission Enquiry",
        message: message,
        priority: fields.priority || "medium",
        status: fields.status || "new",
        source: source,
        source_type: sourceType
    };

    if (board !== undefined && board !== null && board !== "") {
        payload.board = board;
    }

    return payload;
};
</script>
