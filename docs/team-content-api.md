# Team content API

The existing Sanctum authentication and CRM roles remain in use. Only administrators can create, update or delete departments. Older clients can continue using `/team`, `/team/options` and employee update requests without the new fields.

`GET /api/v1/team/options` adds `department_content_supported: true`. Each department keeps `id` and `name` and adds `description`, `photo_url`, `slider_enabled`, `slider_photos` and `revision`. Gallery entries contain an opaque `id`, a filename and a preview URL in display order. Disabled galleries remain in the response so editors retain their content.

Employee responses add `description`; employee create/update accepts a nullable description of up to 5,000 characters. Omitting it preserves the current value. Team photo URLs use the existing controlled media routes, support legacy private media and include a filename and cache version. These public media routes are independent of frontend maintenance mode so CRM previews keep working.

## Department writes

- `POST /api/v1/team/departments` creates a department.
- `POST /api/v1/team/departments/{id}` updates a department.
- `DELETE /api/v1/team/departments/{id}` deletes an empty department.

Updates and deletes require the `revision` from the last read. A stale revision returns HTTP 409 and leaves current data and media untouched. The app retains the draft after an error.

Create/update accepts multipart fields:

- `name`: required, up to 255 characters; the redundant leading `Відділ` is normalized.
- `description`: nullable, up to 10,000 characters.
- `slider_enabled`: `0` or `1`; disabling retains files and their order.
- `photo_file`: optional JPEG/PNG/WebP, up to 10 MiB.
- `remove_photo`: optional `1`; cannot be combined with a new cover.
- `slider_files[]`: new or edited images, each JPEG/PNG/WebP up to 10 MiB.
- `gallery_order`: JSON array of tokens, up to 20 entries. `existing:<id>` references a gallery image belonging to this department; `upload:0` references the first new file. Tokens must be unique and every uploaded file must be referenced exactly once.

The order array also defines explicit removals. Omitting media fields preserves current media. Validation runs before storing files; a failed transaction removes new uploads, and replaced files are removed only after a successful commit. Cover and gallery references are considered together during cleanup. Occupied departments cannot be deleted. Changes reuse the existing system log.

## Rollout

Apply `2026_11_02_210000_add_team_department_content` before serving this API. It only adds nullable content columns and a disabled-by-default slider. Back up the database and media before deployment. Deploy the backend before distributing mobile 1.0.15+18. Older backends do not advertise the feature; the app hides department management there.

Before publishing binaries, run the complete PHP and Flutter suites in an unrestricted build environment, build signed Android APK/AAB and signed iOS IPA on the configured Mac, and verify upload size limits on the server with a full gallery. The app exports bounded PNG images (600×800 for employee portraits, 1600×900 for department covers, 1280×720 for gallery photos); the PHP/web-server aggregate POST limit must accommodate the chosen gallery batch.

Do not change mobile update metadata or replace download files until new signed binaries have been built and verified.
