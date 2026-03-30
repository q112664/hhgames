# AGENTS.md

This file defines workspace-wide agent instructions for this repository.

## Stack

- Stack: Laravel 13, PHP 8.3+, Inertia.js, React 19, TypeScript, Tailwind CSS v4, shadcn-style UI components.

## Rules

- Favor small, vertical slices that are shippable.
- Use Inertia + React pages for app screens unless the repository already uses Blade for that area.
- Reuse existing layouts, shared components, and `resources/js/components/ui` before creating new ones.
- Keep controllers thin. If logic starts growing, move it into an action, service, or dedicated class.
- Match the existing Tailwind + shadcn-style visual language unless the user asks for a different design direction.
- If the user explicitly requests a specific UI library or design system, consult the official docs/examples for that library before implementing.
- For backend or workflow changes, prefer adding or updating Pest feature tests.
- Do not replace existing libraries or project structure unless the user asks.
