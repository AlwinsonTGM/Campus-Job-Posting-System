# Campus Job Posting System

Institutional employment and career mobility portal connecting students, campus employers, and career services administrators.

## Language

### Authentication & Access

**SessionGuard**:
The authority responsible for request authentication gating, role authorization, and return-to destination resolution.
_Avoid_: AuthManager, LoginFilter, SessionHandler

**Verification Quarantine**:
The restricted session state where a registered or unverified account is held pending six-digit institutional email OTP confirmation before receiving active session privileges.
_Avoid_: PendingAuth, UnverifiedSession, TempUser

**Return-To Target**:
The validated relative destination URL preserved across authentication or verification redirects so users resume their original workflow.
_Avoid_: RedirectUri, NextUrl, Callback

**Role Dashboard**:
The canonical landing route assigned to an authenticated account role (`student`, `employer`, `admin`).
_Avoid_: Home, LandingPage, DefaultRoute

**Partner & Staff Portal**:
The dedicated authentication gateway (`portal-login.php`) for university department offices, accredited industry partners, and career services administrators, strictly separated from student applicant sign-in (`login.php`).
_Avoid_: AdminLogin, StaffLogin, BackofficeSignIn

**Administrative Account Provisioning**:
The administrator-initiated creation of pre-verified university office (`university_office`) or industry partner (`approved_partner`) employer accounts issued with a temporary credential.
_Avoid_: ManualSignup, AdminRegister, BypassedAccount

**Temporary Password Gate**:
The restricted session state (`must_change_password`) requiring an administratively provisioned account to establish a personal password upon initial authentication before accessing operational portal routes.
_Avoid_: FirstLoginLock, PasswordResetForce, TempAuthState

### Requisitions & Applications

**ApplicationService**:
The authority responsible for evaluating candidate eligibility, processing applications, and querying applicant pipelines.
_Avoid_: JobAppHandler, CandidateManager, RequisitionManager

**RequisitionEligibility**:
The domain evaluation determining whether an authenticated candidate can apply for a vacancy based on status, deadline, slot capacity, account verification, and prior submissions.
_Avoid_: ApplyPermission, CanApplyCheck, JobGating

**Vacancy Capacity**:
The metric tracking available candidate openings based on approved positions (`slots_total`) versus accepted candidates (`slots_filled`).
_Avoid_: JobLimit, MaxSeats, Quota

### Document & Media Storage

**AttachmentStore**:
The authority responsible for validating, persisting, and organizing uploaded institutional documents and media assets.
_Avoid_: FileUploader, UploadService, MediaManager

**Storage Collection**:
A named classification of uploaded attachments (such as `permits`, `proofs`, `resumes`, `job_photos`, `category_photos`) defining directory paths, size caps, and permitted MIME types.
_Avoid_: UploadFolder, FileType, AttachmentBucket

**StorageResult**:
The domain result of an attachment upload operation reporting success status, relative web path, filename, and specific error diagnostics.
_Avoid_: UploadResponse, FileOutput, SaveStatus

**Candidate Profile Resume**:
The default curriculum vitae attachment persisted on a student account profile, automatically linked to requisition applications when an applicant elects not to upload a one-off override.
_Avoid_: DefaultResume, UserCV, StoredResumeMock

**Academic Credential Proof**:
The verified institutional document persisted on a student profile (`registration_proof`) and linked to requisition applications (`study_load_file`)—either an **Official Certificate of Registration (e-COR)** for enrolled `@kld.edu.ph` students, or an **Official KLD Diploma** for graduated alumni and non-`@kld.edu.ph` (`@gmail.com`) student accounts—inspected alongside the candidate's resume during employer evaluation.
_Avoid_: StudentProof, GenericAttachment, IDUpload

### Datastore & Fixture Lifecycle

**DatastoreManager**:
The authority responsible for dataset mode transitions, baseline fixture synchronization, session reconciliation, and lazy runtime schema guarantees.
_Avoid_: DatabaseHelper, SeedRunner, ModeSwitcher, ResetController

**Dataset Mode**:
The operating state of the datastore, either `demo` (rich realistic sample dataset with preconfigured test accounts) or `real` (clean institutional baseline for live operation).
_Avoid_: Environment, TestMode, SandboxMode

**Baseline Fixture**:
The pristine seed data stored in `data/seeds/{mode}` that can be re-synchronized to restore the database to an uncontaminated initial state.
_Avoid_: MockFile, InitialJson, RawSeed

### Archival & Audit Preservation

**Non-Destructive Archival**:
The domain mechanism ensuring entities (`Job`, `Category`, `Update`) are never permanently purged from the relational datastore; instead, their lifecycle state transitions via `is_archived` and `archived_at` timestamps to preserve candidate histories and audit trails.
_Avoid_: SoftDelete, TrashCan, Bin

**Candidate Withdrawal**:
The student-initiated state transition (`withdrawn`) on an application record that halts hiring consideration while retaining submission history and availability logs for departmental audits.
_Avoid_: CancelApplication, DeleteApplication, DroppedCandidate

**Account Suspension**:
The administrative action transitioning a user account into `suspended` state, barring authentication and quick-login while maintaining institutional records.
_Avoid_: BannedUser, DeletedUser, DisabledAccount

**Administrative Restoration**:
The privileged administrative capability to revive archived requisitions, categories, and bulletins back into active circulation.
_Avoid_: Unarchive, RecoverItem, Undelete

### Quality & Layout Verification

**Hybrid Verification Harness**:
The architectural pattern coupling natural-language agent navigation with deterministic DOM layout invariants and relational datastore verification.
_Avoid_: PureAITesting, MockRunner

**Continuous Viewport Sweep**:
The automated verification process dynamically stepping a viewport across breakpoint ranges (360px through 1280px) to surface fluid layout collisions, clipping, and text truncation during window resizing.
_Avoid_: ResizeTest, SliderCheck

**Zero-Overflow Invariant**:
The non-negotiable structural constraint asserting that neither the root document nor any primary layout container generates an unwanted horizontal scrollbar (`scrollWidth > clientWidth`).
_Avoid_: OverflowCheck, WidthBug


