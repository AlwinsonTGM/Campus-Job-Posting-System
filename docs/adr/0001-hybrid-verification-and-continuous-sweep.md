# 0001: Hybrid Verification and Continuous Viewport Sweep

## Context
Automated tests in the campus job portal suffered from false positives—tests reported green while responsive layouts broke during dynamic resizing (such as when evaluators slide the browser window), causing text clipping, button collisions, and horizontal scrollbars. Pure AI testing previously failed due to unguided prompts and lack of structural guarantees, while pure static selectors proved brittle.

## Decision
We adopt a **Hybrid Verification Harness**:
1. High-level user journeys (Student application, Employer posting, Authentication quarantine) are driven by semantic role actions and cached replays.
2. Layout integrity is enforced deterministically via a **Continuous Viewport Sweep** (`360px` to `1280px`) asserting the **Zero-Overflow Invariant** (`scrollWidth <= clientWidth`) and interactive element collision bounds on every breakpoint transition.
3. Tests are rolled out in three tracer-bullet phases (Core Engine $\to$ Governance & Datastore Lifecycle $\to$ Auxiliary Scenarios).
