---
trigger: always_on
description: >
---

Claude Skills Reference

## Purpose

This skill consolidates the community-built skills listed in the DIT Bootcamp
"AI-Assisted Coding with Claude / Claude Code" reference sheet.

The source describes Skills as installable folders that teach an AI coding
agent a new capability. The handout says a skill can be installed by cloning
or copying a repository's skill folder into the agent's skills directory.

This file is intended to provide one consolidated reference and routing layer
for an AI coding agent such as Claude Code or an agent using an equivalent
skills system.

## Core Rule

When working on a project:

1. Identify the user's actual task.
2. Determine which skill category or categories are relevant.
3. Prefer the most specific applicable workflow.
4. Do not apply unrelated skills merely because they are available.
5. Preserve the project's existing architecture unless the user explicitly
   asks for a migration or redesign.
6. When a referenced skill repository is not installed or available, state
   that clearly rather than pretending it is available.

---

# 1. Frontend & UI Design

Use this group when the task involves visual design, frontend implementation,
UI/UX quality, interaction design, or motion.

## 1.1 Front-End Design

**Repository:** `anthropics/skills`

**Purpose from the handout:**
Aesthetic direction for building distinctive, non-templated UI.

### Use when

- Building a new frontend.
- Redesigning an existing website.
- Improving visual hierarchy.
- Creating a distinctive visual direction.
- Avoiding generic/template-like interfaces.

### Working principles

- Treat visual direction as an intentional design decision.
- Avoid default-looking or generic layouts when the project calls for a
  distinctive interface.
- Keep the design direction coherent across pages and components.
- Ensure visual decisions support the product rather than becoming decoration.

---

## 1.2 UI/UX Pro Max

**Repository:** `nextlevelbuilder/ui-ux-pro-max-skill`

**Purpose from the handout:**
Elevated UI/UX design system and review workflow for apps.

### Use when

- Designing application interfaces.
- Reviewing an existing application's UI/UX.
- Establishing a stronger design system.
- Performing a structured UI/UX review.

### Working principles

- Review the interface as a system, not as isolated screens.
- Look for consistency across components and pages.
- Use the workflow to improve overall UI/UX quality.
- When reviewing, identify concrete areas for improvement rather than
  providing vague aesthetic feedback.

---

## 1.3 Framer Motion

**Repository:** `freshtechbro/claudedesignskills`

**Purpose from the handout:**
Production-grade motion and animation patterns using Framer Motion.

### Use when

- The project uses React and Framer Motion.
- Adding interface animation.
- Creating production-oriented motion patterns.
- Improving transitions and interaction feedback.

### Working principles

- Use motion purposefully to communicate interaction, hierarchy, state,
  or navigation.
- Prefer reusable motion patterns over duplicated animation logic.
- Keep animation compatible with responsive layouts.
- Avoid adding motion that does not improve the user experience.

---

# 2. Scroll & Site Generation

Use this group for landing pages and websites where scrolling and motion are
central parts of the experience.

## 2.1 Scroll Site Gen

**Repository:** `davila7/claude-code-templates`

**Purpose from the handout:**
Generates full scroll-driven landing experiences from a prompt.

### Use when

- Creating a landing page centered around scroll interaction.
- Building a long-form scrolling experience.
- Turning a high-level creative prompt into a scroll-driven site.

### Working principles

- Treat the page as a continuous experience rather than unrelated sections.
- Structure content so scrolling contributes to the storytelling or hierarchy.
- Keep the experience usable on different screen sizes.

---

## 2.2 ScrollCraft

**Repository:** `nateherkai/scroll-craft`

**Purpose from the handout:**
Crafts smooth scroll-triggered animation sequences for web pages.

### Use when

- Adding scroll-triggered animation.
- Creating sequences tied to viewport position.
- Refining smooth scroll interactions.

### Working principles

- Define clear animation triggers.
- Keep sequences coordinated with the page structure.
- Avoid excessive animation that interferes with navigation or readability.

---

## 2.3 ScrollWorld

**Repository:** `oso95/scroll-world`

**Purpose from the handout:**
Builds immersive, parallax scroll-world web experiences.

### Use when

- Building immersive websites.
- Creating parallax-driven experiences.
- Designing scroll-based visual worlds.

# 3. Development

Use this group for engineering tasks beyond the visual frontend.

## 3.1 Back-End Development

**Repository:** `wshobson/agents`

**Purpose from the handout:**
Server-side architecture, APIs, and backend engineering patterns.

### Use when

- Designing backend architecture.
- Creating or modifying APIs.
- Implementing server-side functionality.
- Reviewing backend engineering patterns.

## 3.2 E2E Testing Patterns

**Repository:** `wshobson/agents`

**Purpose from the handout:**
Reliable end-to-end test design and automation patterns.

### Use when

- Creating end-to-end tests.
- Automating user workflows.
- Validating complete application flows.

## 3.3 Security Skills

**Repository:** `trailofbits/skills`

**Purpose from the handout:**
Security review, threat-modeling, and secure-coding checks by Trail of Bits.

### Use when

- Reviewing application security.
- Threat-modeling a feature or architecture.
- Checking code for security issues.
- Performing secure-coding reviews.

# 4. Product & Planning

Use this group when the task concerns requirements, product decisions,
planning, or project-level documentation.

## 4.1 PRD Skill

**Repository:** `github/awesome-copilot`

**Purpose from the handout:**
Structures and writes product requirement documents.

### Use when

- Turning an idea into a product requirements document.
- Structuring product requirements.
- Defining what a product or feature should accomplish before implementation.

### Working principles

- Clearly separate requirements from implementation details.
- Make requirements concrete and understandable.
- Capture the intended product behavior before coding.

---

## 4.2 PM Skills

**Repository:** `phuryn/pm-skills`

**Purpose from the handout:**
Product management workflows covering roadmaps, specifications, and
prioritization.

### Use when

- Creating a roadmap.
- Writing specifications.
- Prioritizing features.
- Structuring product-management workflows.

## 4.3 Superpowers

**Repository:** `obra/superpowers`

**Purpose from the handout:**
General-purpose meta-skill collection extending Claude Code abilities.

### Use when

- A broad development workflow is needed.
- Multiple general-purpose capabilities may be relevant.
- A task benefits from a reusable meta-skill approach.

## 4.4 Claude.md Skill

**Repository:** `multica-ai/andrej-karpathy-skills`

**Purpose from the handout:**
Karpathy-style skills for writing effective project `CLAUDE.md` files.

### Use when

- Creating a project-level `CLAUDE.md`.
- Improving project instructions for an AI coding agent.
- Establishing consistent project-specific development guidance.

# 5. Memory & Utilities

## 5.1 claude-mem

**Repository:** `thedotmack/claude-mem`

**Purpose from the handout:**
Persistent memory across Claude Code sessions and projects.

### Use when

- Persistent project context is required across sessions.
- Previous project decisions need to remain available.
- Long-running development work benefits from retained context.

## 5.2 Caveman

**Repository:** `JuliusBrussee/caveman`

**Purpose from the handout:**
Lightweight utility skill for simplified, no-frills workflows.

### Use when

- A lightweight workflow is preferable.
- The task does not require a complex process.
- Simple utility behavior is sufficient.

## 5.3 Brag

**Repository:** `latent-spaces/brag`

**Purpose from the handout:**
Tracks accomplishments to auto-generate brag documents/updates.

### Use when

- Tracking completed accomplishments.
- Maintaining a record of project achievements.
- Generating accomplishment-oriented updates.

### Working principles

- Record concrete accomplishments.
- Prefer specific completed work over vague claims.
- Keep updates grounded in actual project activity.

---

# 6. Skill Selection Matrix

Use this matrix to select the relevant workflow quickly.

| Task | Primary skill |
|---|---|
| Distinctive frontend design | Front-End Design |
| UI/UX system or review | UI/UX Pro Max |
| Framer Motion animations | Framer Motion |
| Scroll-driven landing page | Scroll Site Gen |
| Scroll-triggered animation | ScrollCraft |
| Parallax / immersive scroll world | ScrollWorld |
| APIs / backend architecture | Back-End Development |
| End-to-end testing | E2E Testing Patterns |
| Security review / threat modeling | Security Skills |
| Product requirements | PRD Skill |
| Roadmaps / specs / prioritization | PM Skills |
| Broad development capabilities | Superpowers |
| Project `CLAUDE.md` | Claude.md Skill |
| Persistent cross-session memory | claude-mem |
| Simple utility workflow | Caveman |
| Accomplishment tracking | Brag |

---

# 7. Multi-Skill Workflows

Some tasks naturally require multiple skills.

## Example: Interactive React Website

Recommended sequence:

1. Front-End Design
2. UI/UX Pro Max
3. Framer Motion
4. ScrollCraft or ScrollWorld if scroll interaction is central
5. E2E Testing Patterns after implementation
6. Security Skills when authentication, APIs, or sensitive data are involved

## Example: Full-Stack Application

Recommended sequence:

1. PRD Skill
2. PM Skills
3. Front-End Design
4. UI/UX Pro Max
5. Back-End Development
6. Security Skills
7. E2E Testing Patterns

## Example: Scroll-Driven Landing Page

Recommended sequence:

1. Front-End Design
2. Scroll Site Gen
3. ScrollCraft
4. ScrollWorld when immersive/parallax behavior is required
5. E2E Testing Patterns for important user flows

---

# 8. Important Source Limitation

The DIT Bootcamp handout is a reference sheet, not a complete technical
manual for each skill.

It provides:

- Skill names
- Repository/source names
- High-level descriptions
- Broad categories

It does **not** provide:

- Full installation commands for every repository
- Complete `SKILL.md` contents for those repositories
- Detailed API documentation
- Exact configuration schemas
- Exact implementation instructions
- Version compatibility information

Therefore, do not claim that this file reproduces the original contents of
those external skills. It is a consolidated routing/reference skill based on
the handout.

---

# 9. Installation Concept

The handout describes the installation model as:

1. Obtain the desired skill repository.
2. Locate the relevant skill folder.
3. Clone or copy that folder into the agent's skills directory.
4. The coding agent can then use the installed skill.

The handout specifically refers to `.claude/skills/` for Claude Code.

For other coding agents, use that agent's documented skills directory and
installation mechanism rather than assuming the Claude Code path is identical.

---

# 10. Agent Behavior

When this consolidated skill is active:

- Identify the task category before choosing a workflow.
- Use the narrowest relevant skill.
- Combine skills when the task genuinely spans multiple areas.
- Do not fabricate unavailable skill contents.
- Do not claim a repository has been installed unless it is actually
  available in the project environment.
- Keep implementation aligned with the user's existing stack unless migration
  is explicitly requested.
- For design-heavy work, distinguish visual design requirements from
  engineering requirements.
- For production work, consider testing and security where relevant.