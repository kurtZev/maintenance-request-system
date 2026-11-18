# QA test results

Run against http://localhost/maintenance-request-system on 2026-11-15 and 2026-11-16. Every row was executed; nothing is marked Pass without a check. The UI rows were measured in a browser at 375 x 812 (phone size).

| Test ID | Feature | Test scenario | Test input | Expected result | Actual result | Pass/Fail |
|---|---|---|---|---|---|---|
| AUTH-01 | Authentication | Logged-out visitor opens an admin page | GET /admin/dashboard | 302 to /login | 302 -&gt; /login | Pass |
| AUTH-02 | Authentication | Logged-out visitor opens a requester page | GET /requester/requests/create | 302 to /login | 302 -&gt; /login | Pass |
| AUTH-03 | Authentication | Register a new requester | valid form | 302 to /requester/dashboard, user row saved | 302 -&gt; /requester/dashboard, row saved | Pass |
| AUTH-04 | Authentication | Registration cannot choose its own role | extra field role=admin | role saved as requester | role=requester | Pass |
| AUTH-05 | Security | Password stored as a hash | registered password | bcrypt hash, verifies, not plain text | $2y$..., verify=true | Pass |
| AUTH-06 | Authentication | Register with an email already in use | same email again | form error, no second account | Another account already uses this email., accounts=1 | Pass |
| AUTH-07 | Input validation | Register with weak password and bad phone | password "short1", phone "abc" | both fields rejected, no account | Enter a valid phone number, e.g. 09171234567. \| The password must be at least 8 characters. | Pass |
| AUTH-08 | Authentication | Login with a wrong password | correct email, wrong password | "Incorrect email or password." | danger: Incorrect email or password. | Pass |
| AUTH-09 | Security | SQL injection in the login form | ' OR '1'='1 | login refused | danger: Incorrect email or password. | Pass |
| AUTH-10 | Security | Login without a CSRF token | correct credentials, no token | login refused | danger: Your session expired. Please try again. | Pass |
| AUTH-11 | PHP errors | Array values posted to login | email[]=x&password[]=y | normal error, no PHP warning | danger: Enter your email and password. | Pass |
| AUTH-12 | Authentication | Admin logs in | correct credentials | 302 to /admin/dashboard | 302 -&gt; /admin/dashboard | Pass |
| AUTH-13 | Security | Session ID changes at login | cookie before vs after | different IDs | changed | Pass |
| AUTH-14 | Authorization | Requester opens an admin page | GET /admin/requests | 302 to own dashboard | 302 -&gt; /requester/dashboard | Pass |
| AUTH-15 | Authorization | Admin opens a requester page | GET /requester/requests/create | 302 to own dashboard | 302 -&gt; /admin/dashboard | Pass |
| AUTH-16 | Authentication | Logout typed as a link does nothing | GET /logout | still logged in | dashboard 200 | Pass |
| AUTH-17 | Authentication | Log out | POST /logout with token | 302 to /login, pages need login again | 302 -&gt; /login; dashboard 302 -&gt; /login | Pass |
| CRE-01 | Create | Requester submits a valid request | all fields valid | 302 to its view page; row saved | 302 -&gt; /requester/requests/view?id=18 | Pass |
| CRE-02 | Create | New request defaults | row in database | MRS-YYYY-NNNN, Pending, owner = session user, date = now | MRS-2026-0018, Pending, requester_id=11, 2026-11-15 18:31:58 | Pass |
| CRE-03 | Create | Success message after submitting | page after redirect | green alert with the reference number | success: Your request was submitted. Your reference number is MRS-2026-0018. | Pass |
| CRE-04 | Input validation | Submit an empty request form | all fields blank | errors on 5 fields, nothing saved | Enter a short title for the problem. \| Choose a category. \| Choose a priority. \| Enter where the problem is (building and room). \| Describe the problem. | Pass |
| CRE-05 | Input validation | Tampered category and priority | category=Hacked, priority=ASAP | both rejected, nothing saved | Choose a category. \| Choose a priority. | Pass |
| CRE-06 | Input validation | Over-long title and description | 151 and 2001 characters | both rejected, nothing saved | The title must be 150 characters or fewer. \| The description must be 2000 characters or fewer. | Pass |
| CRE-07 | PHP errors | Array values posted to the request form | title[]=x ... | field errors, no PHP warning, nothing saved | Enter a short title for the problem. \| Choose a category. \| Choose a priority. \| Enter where the problem is (building and room). \| Describe the problem. | Pass |
| CRE-08 | Security | Create without a CSRF token | valid fields, no token | refused, nothing saved | 302 -&gt; /requester/requests/create; danger: The form could not be submitted (session expired or file too large). Please try again. | Pass |
| CRE-09 | Authorization | Admin posts to the requester create route | valid fields | refused, nothing saved | 302 -&gt; /admin/dashboard | Pass |
| CRE-10 | Create | Submit a request with a PNG photo | 1x1 PNG | saved under a random name outside public/ | photo=random .png name, file exists | Pass |
| CRE-11 | Security | Upload a PHP file renamed to .jpg | shell.jpg containing PHP | rejected, nothing saved | The photo must be a JPG or PNG image. | Pass |
| READ-01 | Read | Requester list shows only own requests | Alpha opens My Requests | Alpha's 2 requests, none of Bravo's | MRS-2026-0019, MRS-2026-0018 | Pass |
| READ-02 | Read | Admin list shows all requests with requester names | GET /admin/requests | total = database count; both QA requesters' names shown | 12 shown vs 12 in DB | Pass |
| READ-03 | Security | Script tag in a title is escaped in the list | title "&lt;script&gt;alert(1)&lt;/script&gt;" | shown as text, not as a tag | escaped | Pass |
| READ-04 | Read | Admin opens a request | GET /admin/requests/view?id | details, requester name and email, timeline | status 200 | Pass |
| READ-05 | Request ownership | Requester opens another user's request | Bravo opens Alpha's request | redirect with "not found", no details | 302 -&gt; /requester/requests; success: Your request was submitted. Your reference number is MRS-2026-0024. \| danger: That request was not found. | Pass |
| READ-06 | Request ownership | Photo is only served to the owner and admins | GET /requests/photo?id as 4 users | owner 200 image/png, admin 200, other requester 404, guest to login | owner 200 image/png, admin 200, other 404, guest 302 -&gt; /login | Pass |
| READ-07 | Error handling | Admin opens a request with an invalid ID | id=abc | redirect to the list with "not found" | 302 -&gt; /admin/requests; danger: That request was not found. | Pass |
| READ-08 | Error handling | Admin opens a request with an invalid ID | id=999999 | redirect to the list with "not found" | 302 -&gt; /admin/requests; danger: That request was not found. | Pass |
| READ-09 | Error handling | Admin opens a request with an invalid ID | id=-1 | redirect to the list with "not found" | 302 -&gt; /admin/requests; danger: That request was not found. | Pass |
| READ-10 | Read | Empty state for a requester with no requests | new account opens My Requests | friendly message with a link | message shown | Pass |
| UPD-01 | Update | Requester edits own pending request | new category, location, description, priority | saved; reference, date, owner and status unchanged | IT/Network, Edited room, High; ref MRS-2026-0018, submitted 2026-11-15 18:31:58 | Pass |
| UPD-02 | Request ownership | Requester posts an edit for another user's request | Bravo edits Alpha's request | refused, row unchanged | 302 -&gt; /requester/requests; title=QA edited by owner | Pass |
| UPD-03 | Update | Admin edits a request | new values | saved; reference and submission date unchanged | QA edited by admin, Other; ref MRS-2026-0018, submitted 2026-11-15 18:31:58 | Pass |
| UPD-04 | Update | Success message after editing | page after redirect | green "was updated" alert | success: Request MRS-2026-0018 was updated. | Pass |
| UPD-05 | Input validation | Admin saves an edit with invalid values | blank location, priority=Nope | errors shown, row unchanged | Choose a priority. \| Enter where the problem is (building and room). | Pass |
| UPD-06 | Error handling | Open the edit form for a missing request | id=999999 | redirect with "not found" | 302 -&gt; /admin/requests; danger: That request was not found. | Pass |
| UPD-07 | Security | Extra fields in an edit cannot change protected columns | reference_no, date_submitted, status, requester_id posted | all four unchanged | MRS-2026-0018, 2026-11-15 18:31:58, Pending, requester_id=11 | Pass |
| DASH-01 | Dashboard statistics | Admin dashboard cards match the database | total / pending / in progress / completed / urgent | 12 / 8 / 2 / 1 / 2 | 12 / 8 / 2 / 1 / 2 | Pass |
| STAT-01 | Status management | Admin sets a request to In Progress with notes | status + remarks | status, remarks and handler saved; no completion date | In Progress, remarks saved=true, handled_by=9, completed=NULL | Pass |
| STAT-02 | Status management | Success alert after a status change | page after redirect | green "is now In Progress" alert | success: Request MRS-2026-0020 is now In Progress. \| light:   | Pass |
| DASH-02 | Dashboard statistics | Cards update after a status change | Pending -&gt; In Progress | pending -1, in progress +1 | pending 8 -&gt; 7, in progress 2 -&gt; 3 | Pass |
| STAT-03 | Status management | Completing a request sets the completion date | status=Completed | date_completed = now | Completed, completed=2026-11-15 18:32:02 | Pass |
| STAT-04 | Status management | Saving a completed request again keeps its date | Completed -&gt; Completed, 2 s later | date_completed unchanged | completed=2026-11-15 18:32:02 | Pass |
| STAT-05 | Status management | Requester sees the status, notes and timeline | Bravo opens own completed request | Completed badge, remarks text, timeline | status 200 | Pass |
| DASH-03 | Dashboard statistics | Requester dashboard counts only that user's requests | mine / pending / in progress / completed | 5 / 4 / 0 / 1 | 5 / 4 / 0 / 1 | Pass |
| STAT-06 | Status management | Reopening a completed request clears the date | Completed -&gt; In Progress | date_completed empty | In Progress, completed=NULL | Pass |
| STAT-07 | Input validation | Reject without giving a reason | status=Rejected, empty remarks | red alert, status unchanged | danger: Give a reason in the remarks when rejecting a request. \| light:  ; status=In Progress | Pass |
| STAT-08 | Input validation | Status value outside the allowed list | status=Hacked | "Choose a valid status.", unchanged | danger: Choose a valid status. \| light:  ; status=In Progress | Pass |
| STAT-09 | Input validation | Status value outside the allowed list | status=Cancelled | "Choose a valid status.", unchanged | danger: Choose a valid status. \| light:  ; status=In Progress | Pass |
| STAT-10 | Input validation | Remarks longer than the limit | 1001 characters | red alert, unchanged | danger: Remarks must be 1000 characters or fewer. \| light:  ; status=In Progress | Pass |
| STAT-11 | Authorization | Requester posts a status change | Bravo -&gt; /admin/requests/status | refused, unchanged | 302 -&gt; /requester/dashboard; status=In Progress | Pass |
| STAT-12 | Status management | Reject with a reason | status=Rejected + remarks | Rejected, remarks saved | Rejected, remarks saved=true | Pass |
| CAN-01 | Request ownership | Requester cancels another user's request | Alpha cancels Bravo's | refused, unchanged | 302 -&gt; /requester/requests; status=Rejected | Pass |
| CAN-02 | Update | Requester cancels own pending request | Alpha cancels own | status Cancelled, success alert | success: Request MRS-2026-0018 was cancelled.; status=Cancelled | Pass |
| CAN-03 | Update | Requester edits a request that is no longer pending | edit a cancelled request | refused, unchanged | warning: Only pending requests can be edited.; title=QA edited by admin | Pass |
| CAN-04 | Status management | Admin changes the status of a cancelled request | Cancelled -&gt; In Progress | refused, stays Cancelled | danger: This request was cancelled by the requester and can no longer be updated.; status=Cancelled | Pass |
| SRCH-01 | Search and filtering | Search by reference number | q=MRS-2026-0019 | exactly that request | MRS-2026-0019 | Pass |
| SRCH-02 | Search and filtering | Search by requester name | q=Qa Bravo ec8420 | Bravo's 5 requests only | 5 rows: MRS-2026-0020, MRS-2026-0021, MRS-2026-0022, MRS-2026-0023, MRS-2026-0024 | Pass |
| SRCH-03 | Search and filtering | Filter by category | category=Plumbing | 2 matching rows, all Plumbing | 2 shown, 0 wrong rows | Pass |
| SRCH-04 | Search and filtering | Filter by priority | priority=Urgent | 2 matching rows, all Urgent | 2 shown, 0 wrong rows | Pass |
| SRCH-05 | Search and filtering | Filter by status | status=Cancelled | 1 matching rows, all Cancelled | 1 shown, 0 wrong rows | Pass |
| SRCH-06 | Search and filtering | Combine search with three filters | name + Cleaning + Low + Pending | Bravo's 4 filler requests | 4 rows | Pass |
| SRCH-07 | Search and filtering | Filter values stay selected after submitting | same query | search text and 3 dropdowns keep their values | checked in returned HTML | Pass |
| SRCH-08 | Search and filtering | No-results message | Alpha's name + Carpentry | "No requests match your filters." | 0 rows; message shown | Pass |
| SRCH-09 | Security | SQL injection text in the search box | q=' OR '1'='1 | no rows, no error, tables intact | 200, 0 rows, users=8 | Pass |
| SRCH-10 | Security | SQL injection text in the search box | q='; DROP TABLE users; -- | no rows, no error, tables intact | 200, 0 rows, users=8 | Pass |
| SRCH-11 | Security | SQL injection text in the search box | q=% | no rows, no error, tables intact | 200, 0 rows, users=8 | Pass |
| SRCH-12 | Input validation | Invalid filter values in the address bar | status=Hacked, category[]=x, priority=&lt;script&gt; | ignored; full list, no error | 200, 12 in total | Pass |
| PAGE-01 | Search and filtering | List is split into pages of 10 | 12 requests | 10 on page 1, 2 on page 2, no overlap | 10 + 2, overlap 0 | Pass |
| PAGE-02 | Error handling | Out-of-range and non-numeric page numbers | page=999, page=abc | last page and first page | page=999 gives last page, page=abc gives first page | Pass |
| SRCH-13 | Request ownership | Requester search cannot reach other users' requests | Alpha searches Bravo's name | no rows | 0 rows | Pass |
| REP-01 | Dashboard statistics | Reports page loads | GET /admin/reports | 200 | 200 | Pass |
| REP-02 | Error handling | Reports with invalid dates | from=garbage, to=invalid | 200, falls back to this month | 200 | Pass |
| REP-03 | Read | CSV export of all requests | from=2000-01-01 to today | CSV with a header and one line per request | text/csv; charset=UTF-8, 12 data lines vs 12 in DB | Pass |
| REP-04 | Security | Formula text in the CSV is neutralised | request titled "=1+1" | cell written as '=1+1 | '=1+1 | Pass |
| REP-05 | Authorization | Requester requests the CSV export | Bravo -&gt; /admin/reports/export | refused | 302 -&gt; /requester/dashboard | Pass |
| DEL-01 | Delete | Delete through a link (GET) | GET /admin/requests/delete?id | 404, row kept | 404, row kept | Pass |
| DEL-02 | Security | Delete without a CSRF token | POST id only | refused, row kept | 302 -&gt; /admin/requests, row kept | Pass |
| DEL-03 | Authorization | Requester posts to the admin delete route | Alpha deletes own request via admin route | refused, row kept | 302 -&gt; /requester/dashboard, row kept | Pass |
| DEL-04 | Input validation | Delete with an invalid ID | id=abc | "not found", nothing deleted | danger: That request was not found.; rows 12 -&gt; 12 | Pass |
| DEL-05 | Input validation | Delete with an invalid ID | id=999999 | "not found", nothing deleted | danger: That request was not found.; rows 12 -&gt; 12 | Pass |
| DEL-06 | Input validation | Delete with an invalid ID | id=1 OR 1=1 | "not found", nothing deleted | danger: That request was not found.; rows 12 -&gt; 12 | Pass |
| DEL-07 | Delete | Admin deletes one request | POST id with token | only that row removed; redirect to list; success alert | removed ids: 19 (target 19); 302 -&gt; /admin/requests; success: Request MRS-2026-0019 was deleted. | Pass |
| DEL-08 | Delete | Deleting a request removes its photo file | file in storage/uploads | file gone | file gone | Pass |
| DB-01 | Database relationships | Delete a user who still has requests (direct SQL) | DELETE FROM users | refused by foreign key | refused (SQLSTATE 23000) | Pass |
| DB-02 | Database relationships | Insert a request for a user that does not exist | requester_id=99999999 | refused by foreign key | refused (SQLSTATE 23000) | Pass |
| DB-03 | Database relationships | Insert a duplicate reference number | existing reference_no | refused by unique key | refused (SQLSTATE 23000) | Pass |
| DB-04 | Database relationships | Delete the admin who handled a request (rolled back) | DELETE admin row | request kept, handled_by becomes empty | request kept, handled_by=NULL | Pass |
| DB-05 | Database relationships | Insert a status outside the allowed list | status='Bogus' | refused by the column type | refused (SQLSTATE 01000) | Pass |
| USER-01 | Database relationships | Admin deletes a user who has requests | delete Bravo in User Management | refused with an explanation, user kept | danger: Qa Bravo ec8420 has submitted requests, so the account cannot be deleted. Deactivate it instead. | Pass |
| USER-02 | Authorization | Admin deactivates their own account | POST own id | refused, still active | danger: You cannot remove your own admin access or deactivate your own account. | Pass |
| USER-03 | Authentication | Deactivated user is logged out on the next click | admin deactivates a logged-in user | status inactive; their next page goes to /login | status=inactive; 302 -&gt; /login | Pass |
| USER-04 | Authentication | Inactive account cannot log in | correct credentials | "This account is inactive" | danger: This account is inactive. Contact the administrator. | Pass |
| USER-05 | Update | Admin edits a user and leaves the password blank | new name, status active, blank password | name and status saved, old password still works | Qa Temp Renamed ec8420, active, old password verifies=true | Pass |
| USER-06 | Authorization | Admin removes their own admin role | edit self, role=requester | refused, still admin | You cannot remove your own admin access or deactivate your own account.; role=admin | Pass |
| USER-07 | Delete | Admin deletes a user with no requests | delete the temp account | user removed, success alert | success: Account for Qa Temp Renamed ec8420 was deleted. | Pass |
| USER-08 | Authorization | Requester opens User Management | Bravo -&gt; /admin/users | refused | 302 -&gt; /requester/dashboard | Pass |
| SEC-01 | Security | Security headers on every page | GET /login | X-Frame-Options, nosniff, Referrer-Policy; no X-Powered-By | frame nosniff referrer no-powered-by | Pass |
| SEC-02 | Security | Session cookie flags | Set-Cookie header | HttpOnly and SameSite=Lax | path=/; HttpOnly; SameSite=Lax | Pass |
| SEC-03 | Security | Source, config and database files cannot be opened | 8 direct file URLs | all 404 | 404 for all 8 | Pass |
| ERR-01 | Error handling | Unknown address | GET /no/such/page | 404 with a friendly page | 404, friendly page | Pass |
| ERR-02 | PHP errors | No PHP warning, notice or fatal error in any response | all responses of this run | none | none found | Pass |
| ERR-03 | MySQL errors | No SQL error text in any response | same responses (SQLSTATE / PDOException) | none | none found | Pass |
| ERR-04 | PHP errors | No new PHP or SQL errors in the server logs during the run | Apache and PHP error logs | no new error lines | no new error lines | Pass |
| UI-01 | Responsive UI | Login page on a phone | 375 px wide | No sideways scrolling; fields fit the screen | No overflow; all fields fit | Pass |
| UI-02 | Responsive UI | Admin dashboard on a phone | 375 px wide | Statistic cards stack one per row; no sideways scrolling | Cards stacked; no overflow | Pass |
| UI-03 | Responsive UI | All Requests on a phone | 375 px wide | Table scrolls inside its card; page does not scroll sideways; sidebar hidden | No page overflow; table inside scroll container; sidebar hidden | Pass |
| UI-04 | Responsive UI | Admin request view with timeline on a phone | 375 px wide | No sideways scrolling; timeline shown | No overflow; 4 timeline steps shown | Pass |
| UI-05 | Responsive UI | Admin edit request form on a phone | 375 px wide | Fields and buttons fit the screen | No overflow; all fields and buttons fit | Pass |
| UI-06 | Responsive UI | Users list on a phone | 375 px wide | Table scrolls inside its card; no page overflow | No overflow; table inside scroll container | Pass |
| UI-07 | Responsive UI | Add User form on a phone | 375 px wide | Fields and buttons fit the screen | No overflow; all fields and buttons fit | Pass |
| UI-08 | Responsive UI | Reports page on a phone | 375 px wide | No sideways scrolling | No overflow | Pass |
| UI-09 | Responsive UI | Menu button opens and closes the sidebar | Tap menu button, then tap the dark overlay | Sidebar slides in with all admin links, closes on overlay tap | Opened 260 px wide with Dashboard, All Requests, Users, Reports; hidden again after overlay tap | Pass |
| UI-10 | Responsive UI | Requester dashboard on a phone | 375 px wide | 4 statistic cards; no sideways scrolling | 4 cards; no overflow | Pass |
| UI-11 | Responsive UI | My Requests on a phone | 375 px wide | Table scrolls inside its card; no page overflow | No overflow; table inside scroll container | Pass |
| UI-12 | Responsive UI | New Request form on a phone | 375 px wide | Fields and buttons fit the screen | No overflow; all fields and buttons fit | Pass |
| UI-13 | Responsive UI | Requester request view on a phone | 375 px wide, long title | Title wraps; Edit and Cancel buttons fit; timeline shown | No overflow; title wrapped to 2 lines; buttons fit; 4 timeline steps | Pass |
