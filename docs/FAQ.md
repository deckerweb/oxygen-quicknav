# FAQ by topic

[Deutsch](FAQ-de.md)

## Getting started

### Does it choose the right Oxygen automatically?

Yes. The active builder determines data queries, categories, permissions, settings, and edit URLs. If both integrations are present, they receive separate submenus. This does not make otherwise incompatible vendor plugins coexist.

### Why are there no separate headers and footers in Classic?

Classic stores templates and reusable parts in ct_template. QuickNav follows that structure instead of guessing a header or footer from its name.

## Everyday use & settings

### Can editors use it?

An administrator can change the required toolbar capability. Each destination still checks WordPress and Oxygen permissions; changing QuickNav visibility does not grant builder access.

### Can I show drafts, posts, or products?

Yes. Choose public content types and optionally unpublished content in the settings. Only editable items with builder data appear. Products require their registered post type and user permission.

### Does network activation share my settings?

No. Defaults and personal preferences are scoped to each website. New websites use defaults automatically. The shared Library manages its own website/network scope.

## Compatibility & data

### What happens to my data when I uninstall?

QuickNav settings remain by default. Optional deletion removes only each opted-in website’s QuickNav settings and personal preferences. Oxygen content stays. Library cleanup retains shared data while another embedded host is installed.

### Does it contact external services?

The updater checks this public GitHub repository through WordPress update requests; GitHub receives normal request metadata. No token is required and no telemetry is added. The Library’s optional online catalog starts disabled; enabling it allows its documented GitHub catalog and package requests.
