You are a developer working on an WinterCMS/OctoberCMS plugin named Campaignr located at:

plugins/impulsetechnologies/campaignr

Your task is to design and implement a reusable Event Registration feature that integrates cleanly with the existing Campaignr architecture.

1. Registration Form

Create a reusable event registration form that can be attached to any Campaignr event.

Form Fields

first_name → string (required)

other_name → string (nullable)

last_name → string (required)

phone_number → string (required)

email → string (required)

organisation_name → string (nullable)

additional_information → text (nullable)

event_id → integer (required, FK to events table)

created_at / updated_at → timestamps

Apply basic validation (required fields, valid email format).

2. Database Migration

Create a migration file in:

plugins/impulsetechnologies/campaignr/updates

The migration should:

Create a new table for event registrations

Include appropriate data types

Add an index on event_id

Update:

plugins/impulsetechnologies/campaignr/updates/version.yaml

Increment the plugin version and reference the new migration.

3. Model

Create a model in:

plugins/impulsetechnologies/campaignr/models

Responsibilities:

Map to the registrations table

Define $fillable properties

Define relationship:

belongsTo → Event model

4. Frontend Integration

Add an “Event Registration” button and form to the following components:

components/eventlist

components/eventcomponent

Requirements:

Button opens the registration form (modal)

The form must automatically bind to the selected event (event_id)

Use shared / reusable form logic (avoid duplication)

5. Form Submission Logic

On successful submission:

Persist registration data to the database

Send a confirmation email to:

The registrant

The site admin

Use WinterCMS/OctoberCMS Mail templates (do not hardcode email content)

6. Admin Export Functionality

Extend the Campaignr admin backend to allow exporting registration data.

Requirements:

Export registrations by event

Reuse or extend the existing export logic found in:

plugins/impulsetechnologies/campaignr/models/EventExport.php

Output format: CSV or Excel

7. Quality Expectations

Follow WinterCMS/WinterCMS/OctoberCMS plugin conventions

Use clean, readable, maintainable code

Avoid breaking existing Campaignr functionality

Ensure the feature is extensible for future fields
