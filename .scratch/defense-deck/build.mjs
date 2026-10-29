export async function build(presentation) {
 const C={paper:'#F5F4F0',ink:'#17241D',green:'#183D2E',muted:'#59655D',mint:'#DFEBDD',gold:'#F1E4C5',red:'#F2DED9',line:'#698471',white:'#FFFFFF'};
 presentation.slides.keep([]);
 presentation.theme.defaultFont='Arial';
 const text=(s,str,x,y,w,h,size=28,color=C.ink,bold=false,align='left')=>{
  const q=s.shapes.add({geometry:'textbox',name:str.slice(0,55),position:{left:x,top:y,width:w,height:h},fill:'none',line:{fill:'none',width:0}});
  q.text=str;q.text.style={fontSize:size,typeface:'Arial',color,bold,alignment:align,verticalAlignment:'middle',wrap:true};q.text.insets=0;return q;
 };
 const box=(s,str,x,y,w=225,h=90,fill=C.mint)=>{
  const q=s.shapes.add({geometry:'rect',name:str.replaceAll('\n',' '),position:{left:x,top:y,width:w,height:h},fill,line:{fill:C.line,width:1}});
  q.text=str;q.text.style={fontSize:26,typeface:'Arial',color:C.ink,bold:true,alignment:'center',verticalAlignment:'middle',wrap:true};q.text.insets=12;return q;
 };
 const link=(s,a,b,from='right',to='left',color=C.green,kind='straight')=>s.shapes.connect(a,b,{fromSide:from,toSide:to,kind,line:{fill:color,width:2.5},tail:{type:'triangle',width:'med',length:'med'}});
 const base=(title,num)=>{const s=presentation.slides.add({width:1280,height:720});s.background.fill=C.paper;text(s,title,64,42,1140,80,44,C.green,true);text(s,String(num).padStart(2,'0'),1183,656,38,30,19,C.muted,false,'right');return s;};
 const notes=(s,t,script,sources)=>{s.speakerNotes.text=`TIMING: ${t}\n\nSCRIPT\n${script}\n\nSOURCE REFERENCES\n${sources.map(p=>'C:/xampp/htdocs/Final-Campus-Job-Posting-System/'+p).join('\n')}`;};

 let s=base('KLD Campus Hire',1);
 text(s,'Campus Job\nPosting System',64,163,575,176,60,C.green,true);
 text(s,'A campus employment portal that connects\nstudents with offices and approved partners',64,376,554,105,28);
 text(s,'COAL101 · BSIS201\nKolehiyo ng Lungsod ng Dasmariñas',64,569,580,66,23,C.muted);
 s.images.add({path:'C:/xampp/htdocs/Final-Campus-Job-Posting-System/.scratch/defense-deck/website-home.png',alt:'Actual KLD Campus Hire homepage',position:{left:669,top:170,width:545,height:405},fit:'cover'});
 notes(s,'0:00–0:20 · 20 seconds',
 'Good day, panelists. We are presenting KLD Campus Hire, our campus job posting system for Kolehiyo ng Lungsod ng Dasmariñas. It connects students with campus offices and approved partners. Our defense focuses on the logic that controls access, applications, hiring decisions, and the records that connect them.',
 ['README.md','index.php']);

 s=base('The complete hiring flow',2);
 text(s,'Each role owns a clear part of the process',64,139,1100,50,29,C.muted);
 const a=box(s,'Employer\npublishes vacancy',64,288,242,110);
 const b=box(s,'Student\nsubmits application',367,288,242,110);
 const c=box(s,'Employer\nreviews candidate',670,288,242,110);
 const d=box(s,'Hiring\ndecision',973,288,242,110,C.gold);
 link(s,a,b);link(s,b,c);link(s,c,d);
 text(s,'Administrator',64,479,300,42,29,C.green,true);
 text(s,'Verifies accounts and documents; manages institutional records and reports',64,531,1100,72,28);
 notes(s,'0:20–0:50 · 30 seconds',
 'The system has three roles: student, employer, and administrator. An employer publishes a vacancy. A student discovers the opportunity and submits an application. The employer then evaluates the candidate and records a decision. Administrators govern access, verify supporting documents, and monitor hiring activity. The application record connects the student to a particular vacancy, so both sides see the same hiring status instead of maintaining separate lists.',
 ['includes/auth-check.php','student/apply.php','employer/create-job.php','employer/review-app.php','admin/users.php','admin/reports.php']);

 s=base('Account verification and access',3);
 const r=box(s,'Register',64,233,190,86);
 const otp=box(s,'Verify email\nwith OTP',307,233,218,86);
 const check=box(s,'Administrative\nreview required?',582,233,262,86,C.gold);
 const dash=box(s,'Role\ndashboard',984,233,230,86);
 const review=box(s,'Document review',582,431,262,86,C.gold);
 const hold=box(s,'Await approval\nor revise proof',64,431,286,86,C.red);
 link(s,r,otp);link(s,otp,check);link(s,check,dash);text(s,'No / verified',855,194,135,34,22,C.muted);
 link(s,check,review,'bottom','top');text(s,'Yes',720,356,65,34,22,C.muted);
 link(s,review,hold,'left','right');text(s,'Pending / rejected',312,391,240,34,22,C.muted);
 link(s,review,dash,'right','bottom',C.green,'elbow');text(s,'Approved',874,390,140,34,22,C.muted);
 text(s,'Approval enables access; the account role controls its permitted pages',64,577,1130,62,28,C.green,true);
 notes(s,'0:50–1:30 · 40 seconds',
 'Registration does not immediately give full access. First, the user confirms the email through a six-digit one-time password. The system then checks administrative verification. Institutional student emails follow the trusted verification path, while accounts requiring evidence, including partner employers and students using personal email, enter administrative review. Pending or rejected accounts remain restricted and can follow the document review process. After approval, the session guard routes users to the correct dashboard and checks their role on protected pages. Administratively provisioned staff accounts must also replace their temporary password before using operational pages.',
 ['register.php','verify-email.php','includes/services/user-service.php','includes/auth-check.php','pending-approval.php']);

 s=base('Student application and eligibility',4);
 const search=box(s,'Search and filter\nopportunities',64,209,230,92);
 const details=box(s,'Read vacancy\ndetails',352,209,230,92);
 const gate=box(s,'Eligible\nto apply?',640,209,230,92,C.gold);
 const submit=box(s,'Complete form\nand submit',928,209,286,92);
 link(s,search,details);link(s,details,gate);link(s,gate,submit);text(s,'Yes',878,168,55,34,22,C.muted);
 text(s,'No → reason shown; submission blocked',640,316,575,35,23,C.muted);
 text(s,'Eligibility gate',64,370,430,44,30,C.green,true);
 text(s,'Active vacancy · deadline valid · slots available\nVerified student · no duplicate · no other appointment',64,430,1130,91,27);
 text(s,'Application input',64,554,280,40,29,C.green,true);
 text(s,'Statement of intent, contact details, weekly availability and linked documents',365,550,846,74,27);
 notes(s,'1:30–2:20 · 50 seconds',
 'Students search by keyword and use filters to narrow the available opportunities. Before opening the application form, the service checks that the vacancy is active, not archived, within its deadline, and below capacity. It also requires a verified student account, prevents a duplicate submission, and blocks a new application when the student already holds another campus appointment. A failed check shows the reason and stops submission. Eligible students provide a statement of intent, contact information, and at least one weekly availability slot. The schedule covers Monday to Saturday across morning, afternoon, and evening. Resumes and academic evidence can link from the profile. Successful submission creates a Pending Review record that appears in My Applications.',
 ['student/jobs.php','student/job-details.php','student/apply.php','includes/services/application-service.php','includes/services/job-service.php']);

 s=base('Employer posting and candidate review',5);
 const employer=box(s,'Verified\nemployer',64,212,227,98);
 const compose=box(s,'Define vacancy\nand requirements',371,212,280,98);
 const publish=box(s,'Publish\nopportunity',731,212,227,98);
 link(s,employer,compose);link(s,compose,publish);
 text(s,'Vacancy information',64,356,320,40,29,C.green,true);
 text(s,'Duties, qualifications, pay, weekly hours, deadline and available slots',64,409,1110,57,28);
 const candidates=box(s,'Applicants for\nowned vacancies',64,528,290,92);
 const dossier=box(s,'Review profile, proof,\nresume and availability',445,528,363,92);
 const decide=box(s,'Record hiring\ndecision',899,528,315,92,C.gold);
 link(s,candidates,dossier);link(s,dossier,decide);
 notes(s,'2:20–3:05 · 45 seconds',
 'On the employer side, verified offices or accredited partners create vacancies with duties, qualifications, compensation, weekly hours, a deadline, and the number of slots. The posting form limits its weekly workload choices to a maximum of twenty hours. Employers manage applications for vacancies they own; the server checks ownership before opening a candidate dossier. The dossier combines the applicant profile, academic proof, resume, statement of intent, and declared availability. Availability indicators support the supervisor’s judgment. They do not automatically hire a student or guarantee a conflict-free class schedule. The supervisor makes the hiring decision.',
 ['employer/create-job.php','employer/applicants.php','employer/review-app.php','includes/services/system-checks.php','includes/services/common-service.php']);

 s=base('Application states and hiring effects',6);
 const pending=box(s,'Pending\nReview',64,207,220,90);
 const under=box(s,'Under\nReview',352,207,220,90);
 const interview=box(s,'Interview\nScheduled',640,207,240,90,C.gold);
 const decision=box(s,'Accepted / Hired',969,207,245,90);
 const declined=box(s,'Declined',969,377,245,78,C.red);
 const withdrawn=box(s,'Withdrawn',64,377,220,78,C.red);
 link(s,pending,under);link(s,under,interview);link(s,interview,decision);
 link(s,interview,declined,'bottom','left',C.green,'elbow');
 link(s,pending,withdrawn,'bottom','top');text(s,'Student action',208,329,220,31,22,C.muted);
 text(s,'Typical progression; the supervisor can decide without an interview',64,137,1140,44,27,C.muted);
 text(s,'When accepted',64,512,290,43,30,C.green,true);
 text(s,'Update filled slots and close a full vacancy\nWithdraw the student’s other active applications; send notifications',64,570,1135,78,27);
 notes(s,'3:05–3:50 · 45 seconds',
 'This diagram shows the typical application journey, rather than a mandatory sequence. An application begins as Pending Review. The supervisor can mark it Under Review, schedule an interview, accept it, or decline it. Interview details include the date, time, and venue. Students can voluntarily withdraw while an application is still pending. Acceptance has additional effects: the service checks capacity, updates the filled-slot count, and closes a vacancy when all slots are filled. It also automatically withdraws that student’s other pending or in-progress applications to support one active campus appointment. Status changes generate notifications and appear in the student tracker.',
 ['student/my-applications.php','employer/review-app.php','includes/services/job-service.php']);

 s=base('Administrator review and record control',7);
 const proof=box(s,'Account or profile\nrequest + proof',64,221,280,104);
 const admin=box(s,'Administrator\nreviews evidence',433,221,280,104,C.gold);
 const approve=box(s,'Approve\nand update record',919,186,295,94);
 const reject=box(s,'Reject\nwith feedback',919,354,295,94,C.red);
 link(s,proof,admin);link(s,admin,approve,'right','left',C.green,'elbow');link(s,admin,reject,'bottom','left',C.green,'elbow');
 text(s,'Accountability',64,438,500,48,30,C.green,true);
 text(s,'Core academic changes require a documented request\nArchived postings retain application history\nReports summarize vacancies, applicants and hiring outcomes',64,505,1130,132,28);
 notes(s,'3:50–4:30 · 40 seconds',
 'Administrators provide the review layer for accounts and sensitive academic changes. A request arrives with supporting evidence, such as registration proof or partner accreditation documents. The administrator approves the request and updates the record, or rejects it with feedback for revision. Students therefore cannot silently replace protected academic details. Administrators also manage categories and announcements, monitor hiring reports, and suspend accounts when needed. Vacancies use archival instead of permanent deletion, preserving the applications connected to them. These controls keep the workflow traceable and give the institution a consistent view of recruitment activity.',
 ['admin/users.php','admin/categories.php','admin/reports.php','includes/services/user-service.php','includes/services/job-service.php']);

 s=base('How the system keeps each view consistent',8);
 const browser=box(s,'Browser\nrequest',64,229,220,104);
 const guard=box(s,'Session and\nrole checks',365,229,230,104);
 const service=box(s,'PHP services\nand validation',676,229,230,104);
 const db=box(s,'MySQL /\nMariaDB',987,229,227,104,C.gold);
 link(s,browser,guard);link(s,guard,service);link(s,service,db);
 text(s,'Saved records → refreshed dashboards, application tracker and notifications',64,389,1145,83,29,C.green,true);
 text(s,'Defense takeaway',64,516,600,44,30,C.green,true);
 text(s,'Every action has an authorized actor, a validation rule and a recorded outcome',64,573,1130,72,32);
 notes(s,'4:30–5:00 · 30 seconds',
 'Technically, the browser sends requests to PHP. The session guard checks identity and role, while service functions validate the action and use PDO to read or update MySQL or MariaDB. Successful writes feed the dashboards, application tracker, notifications, and reports. Protected actions also use CSRF validation, and record access checks ownership. Our main contribution is a connected campus hiring workflow: every important action has an authorized actor, a validation rule, and a recorded outcome. Thank you; we are ready for your questions.',
 ['includes/db.php','includes/auth-check.php','includes/services/application-service.php','includes/services/job-service.php','includes/services/system-service.php']);
 return {slideCount:presentation.slides.items.length,timingSeconds:300};
}
