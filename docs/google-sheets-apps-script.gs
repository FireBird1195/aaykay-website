/**
 * AAYKAY website → Google Sheets
 *
 * Adds every website enquiry as a new row in this spreadsheet.
 * Setup (about 5 minutes, done once, from the company Google account):
 *   1. Create a Google Sheet, e.g. "AAYKAY website enquiries".
 *   2. Extensions → Apps Script. Delete what is there, paste this whole file.
 *   3. In WordPress → Site settings → "Google Sheets secret code", copy the code and
 *      paste it between the quotes below.
 *   4. Click Save (disk icon), then Deploy → New deployment → type "Web app".
 *      Execute as: Me. Who has access: Anyone. Click Deploy and allow access.
 *   5. Copy the "Web app URL" (starts with https://script.google.com/macros/s/) into
 *      WordPress → Site settings → "Google Sheets connection", save, and click
 *      "Send a test row to the Google Sheet".
 *
 * "Anyone" only means the website can reach the script without logging in. Nobody can
 * read the sheet through it, and rows are only added when the secret code matches.
 * If you edit this script later, use Deploy → Manage deployments → Edit → New version,
 * so the same URL keeps working.
 */
const SECRET = 'PASTE-THE-SECRET-CODE-FROM-WORDPRESS-HERE';
const SHEET_NAME = 'Enquiries';
const HEADERS = ['Received', 'Name', 'Company', 'Work email', 'Phone', 'Project type', 'City', 'Scope and timeline', 'Website'];

function doPost(e) {
  const lock = LockService.getScriptLock();
  try {
    const data = JSON.parse(e.postData.contents);
    if (data.secret !== SECRET) return reply({ ok: false, error: 'secret does not match' });
    lock.waitLock(10000); // two enquiries at the same moment never overwrite each other
    const book = SpreadsheetApp.getActiveSpreadsheet();
    const sheet = book.getSheetByName(SHEET_NAME) || book.insertSheet(SHEET_NAME);
    if (sheet.getLastRow() === 0) {
      sheet.appendRow(HEADERS);
      sheet.setFrozenRows(1);
      sheet.getRange(1, 1, 1, HEADERS.length).setFontWeight('bold');
    }
    // A cell starting with = + - @ would be treated as a formula: store it as text.
    const safe = (v) => { v = v == null ? '' : String(v); return /^[=+\-@]/.test(v) ? "'" + v : v; };
    sheet.appendRow([data.received, data.name, data.company, data.email, data.phone, data.type, data.city, data.message, data.source].map(safe));
    return reply({ ok: true });
  } catch (err) {
    return reply({ ok: false, error: String(err) });
  } finally {
    try { lock.releaseLock(); } catch (_) {}
  }
}

function reply(obj) {
  return ContentService.createTextOutput(JSON.stringify(obj)).setMimeType(ContentService.MimeType.JSON);
}
