# Roles and Permissions

This software is capable of assigning permissions to certain roles. As a result there are many roles and permissions defined and these combinations are currently configured in `/src/cms/config/permissions.php`

## All roles and the assigned permissions

This table shows each permission per role:

| Role                       | Permissions                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   |
| -------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| CHIEF_PRIVACY_OFFICER      | CORE_ENTITY_CREATE, CORE_ENTITY_IMPORT, CORE_ENTITY_VIEW, DATA_BREACH_RECORD_CREATE, DATA_BREACH_RECORD_VIEW, DOCUMENT_CREATE, DOCUMENT_VIEW, EXPORT, LOOKUP_LIST_CREATE, LOOKUP_LIST_VIEW, MANAGEMENT_CREATE, MANAGEMENT_VIEW, RESPONSIBLE_CREATE, RESPONSIBLE_VIEW, SNAPSHOT_CREATE, SNAPSHOT_VIEW, SNAPSHOT_STATE_TO_APPROVE, SNAPSHOT_STATE_TO_ESTABLISHED, SNAPSHOT_STATE_TO_OBSOLETE, SNAPSHOT_APPROVAL_CREATE, SNAPSHOT_APPROVAL_VIEW, SNAPSHOT_APPROVAL_ORGANISATION_OVERVIEW, TAG_CREATE, TAG_VIEW, USER_ROLE_ORGANISATION_CPO_MANAGE, USER_ROLE_ORGANISATION_MANAGE |
| COUNSELOR                  | CORE_ENTITY_VIEW, DATA_BREACH_RECORD_VIEW, DOCUMENT_VIEW, MANAGEMENT_VIEW, RESPONSIBLE_VIEW, SNAPSHOT_VIEW, SNAPSHOT_APPROVAL_VIEW                                                                                                                                                                                                                                                                                                                                                                                                                                            |
| DATA_PROTECTION_OFFICIAL   | CORE_ENTITY_VIEW, DATA_BREACH_RECORD_VIEW, DOCUMENT_VIEW, MANAGEMENT_VIEW, RESPONSIBLE_VIEW, SNAPSHOT_VIEW, SNAPSHOT_APPROVAL_VIEW, CORE_ENTITY_FG_REMARKS                                                                                                                                                                                                                                                                                                                                                                                                                    |
| FUNCTIONAL_MANAGER         | ADMIN_LOG_ENTRY_VIEW, ORGANISATION_CREATE, ORGANISATION_DELETE, ORGANISATION_UPDATE, ORGANISATION_UPDATE_IP_WHITELIST, ORGANISATION_VIEW, OTP_DISABLE, PUBLIC_WEBSITE_UPDATE, RESPONSIBLE_LEGAL_ENTITY_CREATE, RESPONSIBLE_LEGAL_ENTITY_DELETE, RESPONSIBLE_LEGAL_ENTITY_UPDATE, RESPONSIBLE_LEGAL_ENTITY_VIEW, USER_CREATE, USER_DELETE, USER_IMPORT, USER_UPDATE, USER_VIEW, USER_ROLE_GLOBAL_MANAGE, USER_ROLE_ORGANISATION_CPO_MANAGE, USER_ROLE_ORGANISATION_MANAGE                                                                                                      |
| INPUT_PROCESSOR            | CORE_ENTITY_CREATE, CORE_ENTITY_VIEW, DOCUMENT_CREATE, DOCUMENT_VIEW, MANAGEMENT_CREATE, MANAGEMENT_VIEW, RESPONSIBLE_CREATE, RESPONSIBLE_VIEW, SNAPSHOT_APPROVAL_CREATE, SNAPSHOT_APPROVAL_VIEW, SNAPSHOT_CREATE, SNAPSHOT_VIEW, ORGANISATION_USER_VIEW                                                                                                                                                                                                                                                                                                                      |
| INPUT_PROCESSOR_DATABREACH | CORE_ENTITY_VIEW, DATA_BREACH_RECORD_CREATE, DATA_BREACH_RECORD_VIEW, DOCUMENT_CREATE, DOCUMENT_VIEW, MANAGEMENT_VIEW, RESPONSIBLE_CREATE, RESPONSIBLE_VIEW                                                                                                                                                                                                                                                                                                                                                                                                                   |
| MANDATE_HOLDER             | CORE_ENTITY_VIEW, DOCUMENT_VIEW, MANAGEMENT_VIEW, RESPONSIBLE_VIEW, SNAPSHOT_VIEW, SNAPSHOT_APPROVAL_VIEW, SNAPSHOT_APPROVAL_UPDATE_PERSONAL, USER_PROFILE_SETTINGS_MANDATEHOLDER                                                                                                                                                                                                                                                                                                                                                                                             |
| PRIVACY_OFFICER            | CORE_ENTITY_CREATE, CORE_ENTITY_IMPORT, CORE_ENTITY_VIEW, DATA_BREACH_RECORD_CREATE, DATA_BREACH_RECORD_VIEW, DOCUMENT_CREATE, DOCUMENT_VIEW, EXPORT, LOOKUP_LIST_CREATE, LOOKUP_LIST_VIEW, MANAGEMENT_CREATE, MANAGEMENT_VIEW, RESPONSIBLE_CREATE, RESPONSIBLE_VIEW, SNAPSHOT_CREATE, SNAPSHOT_VIEW, SNAPSHOT_STATE_TO_APPROVE, SNAPSHOT_STATE_TO_ESTABLISHED, SNAPSHOT_STATE_TO_OBSOLETE, SNAPSHOT_APPROVAL_CREATE, SNAPSHOT_APPROVAL_VIEW, SNAPSHOT_APPROVAL_ORGANISATION_OVERVIEW, TAG_CREATE, TAG_VIEW, USER_ROLE_ORGANISATION_MANAGE                                    |

## All permissions and what roles they are assigned to

This table shows the permissions and the roles to which they are assigned:

| Permission                              | Roles                                                                                                                                    |
| --------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------- |
| CORE_ENTITY_CREATE                      | CHIEF_PRIVACY_OFFICER, INPUT_PROCESSOR, PRIVACY_OFFICER                                                                                  |
| CORE_ENTITY_FG_REMARKS                  | DATA_PROTECTION_OFFICIAL                                                                                                                 |
| CORE_ENTITY_IMPORT                      | CHIEF_PRIVACY_OFFICER, PRIVACY_OFFICER                                                                                                   |
| CORE_ENTITY_VIEW                        | CHIEF_PRIVACY_OFFICER, COUNSELOR, DATA_PROTECTION_OFFICIAL, INPUT_PROCESSOR, INPUT_PROCESSOR_DATABREACH, MANDATE_HOLDER, PRIVACY_OFFICER |
| DATA_BREACH_RECORD_CREATE               | CHIEF_PRIVACY_OFFICER, INPUT_PROCESSOR_DATABREACH, PRIVACY_OFFICER                                                                       |
| DATA_BREACH_RECORD_VIEW                 | CHIEF_PRIVACY_OFFICER, COUNSELOR, DATA_PROTECTION_OFFICIAL, INPUT_PROCESSOR_DATABREACH, PRIVACY_OFFICER                                  |
| DOCUMENT_CREATE                         | CHIEF_PRIVACY_OFFICER, INPUT_PROCESSOR, INPUT_PROCESSOR_DATABREACH, PRIVACY_OFFICER                                                      |
| DOCUMENT_VIEW                           | CHIEF_PRIVACY_OFFICER, COUNSELOR, DATA_PROTECTION_OFFICIAL, INPUT_PROCESSOR, INPUT_PROCESSOR_DATABREACH, MANDATE_HOLDER, PRIVACY_OFFICER |
| EXPORT                                  | CHIEF_PRIVACY_OFFICER, PRIVACY_OFFICER                                                                                                   |
| LOOKUP_LIST_CREATE                      | CHIEF_PRIVACY_OFFICER, PRIVACY_OFFICER                                                                                                   |
| LOOKUP_LIST_VIEW                        | CHIEF_PRIVACY_OFFICER, PRIVACY_OFFICER                                                                                                   |
| MANAGEMENT_CREATE                       | CHIEF_PRIVACY_OFFICER, INPUT_PROCESSOR, PRIVACY_OFFICER                                                                                  |
| MANAGEMENT_VIEW                         | CHIEF_PRIVACY_OFFICER, COUNSELOR, DATA_PROTECTION_OFFICIAL, INPUT_PROCESSOR, INPUT_PROCESSOR_DATABREACH, MANDATE_HOLDER, PRIVACY_OFFICER |
| ORGANISATION_USER_VIEW                  | INPUT_PROCESSOR                                                                                                                          |
| RESPONSIBLE_CREATE                      | CHIEF_PRIVACY_OFFICER, INPUT_PROCESSOR, INPUT_PROCESSOR_DATABREACH, PRIVACY_OFFICER                                                      |
| RESPONSIBLE_VIEW                        | CHIEF_PRIVACY_OFFICER, COUNSELOR, DATA_PROTECTION_OFFICIAL, INPUT_PROCESSOR, INPUT_PROCESSOR_DATABREACH, MANDATE_HOLDER, PRIVACY_OFFICER |
| SNAPSHOT_APPROVAL_CREATE                | CHIEF_PRIVACY_OFFICER, INPUT_PROCESSOR, PRIVACY_OFFICER                                                                                  |
| SNAPSHOT_APPROVAL_ORGANISATION_OVERVIEW | CHIEF_PRIVACY_OFFICER, PRIVACY_OFFICER                                                                                                   |
| SNAPSHOT_APPROVAL_UPDATE_PERSONAL       | MANDATE_HOLDER                                                                                                                           |
| SNAPSHOT_APPROVAL_VIEW                  | CHIEF_PRIVACY_OFFICER, COUNSELOR, DATA_PROTECTION_OFFICIAL, INPUT_PROCESSOR, MANDATE_HOLDER, PRIVACY_OFFICER                             |
| SNAPSHOT_CREATE                         | CHIEF_PRIVACY_OFFICER, INPUT_PROCESSOR, PRIVACY_OFFICER                                                                                  |
| SNAPSHOT_STATE_TO_APPROVE               | CHIEF_PRIVACY_OFFICER, PRIVACY_OFFICER                                                                                                   |
| SNAPSHOT_STATE_TO_ESTABLISHED           | CHIEF_PRIVACY_OFFICER, PRIVACY_OFFICER                                                                                                   |
| SNAPSHOT_STATE_TO_OBSOLETE              | CHIEF_PRIVACY_OFFICER, PRIVACY_OFFICER                                                                                                   |
| SNAPSHOT_VIEW                           | CHIEF_PRIVACY_OFFICER, COUNSELOR, DATA_PROTECTION_OFFICIAL, INPUT_PROCESSOR, MANDATE_HOLDER, PRIVACY_OFFICER                             |
| TAG_CREATE                              | CHIEF_PRIVACY_OFFICER, PRIVACY_OFFICER                                                                                                   |
| TAG_VIEW                                | CHIEF_PRIVACY_OFFICER, PRIVACY_OFFICER                                                                                                   |
| USER_PROFILE_SETTINGS_MANDATEHOLDER     | MANDATE_HOLDER                                                                                                                           |
| USER_ROLE_ORGANISATION_CPO_MANAGE       | CHIEF_PRIVACY_OFFICER, FUNCTIONAL_MANAGER                                                                                                |
| USER_ROLE_ORGANISATION_MANAGE           | CHIEF_PRIVACY_OFFICER, FUNCTIONAL_MANAGER, PRIVACY_OFFICER                                                                               |

The following permissions are only available to the FUNCTIONAL_MANAGER:

| Permission                       | Roles              |
| -------------------------------- | ------------------ |
| ADMIN_LOG_ENTRY_VIEW             | FUNCTIONAL_MANAGER |
| ORGANISATION_CREATE              | FUNCTIONAL_MANAGER |
| ORGANISATION_DELETE              | FUNCTIONAL_MANAGER |
| ORGANISATION_UPDATE              | FUNCTIONAL_MANAGER |
| ORGANISATION_UPDATE_IP_WHITELIST | FUNCTIONAL_MANAGER |
| ORGANISATION_VIEW                | FUNCTIONAL_MANAGER |
| OTP_DISABLE                      | FUNCTIONAL_MANAGER |
| PUBLIC_WEBSITE_UPDATE            | FUNCTIONAL_MANAGER |
| RESPONSIBLE_LEGAL_ENTITY_CREATE  | FUNCTIONAL_MANAGER |
| RESPONSIBLE_LEGAL_ENTITY_DELETE  | FUNCTIONAL_MANAGER |
| RESPONSIBLE_LEGAL_ENTITY_UPDATE  | FUNCTIONAL_MANAGER |
| RESPONSIBLE_LEGAL_ENTITY_VIEW    | FUNCTIONAL_MANAGER |
| USER_CREATE                      | FUNCTIONAL_MANAGER |
| USER_DELETE                      | FUNCTIONAL_MANAGER |
| USER_IMPORT                      | FUNCTIONAL_MANAGER |
| USER_UPDATE                      | FUNCTIONAL_MANAGER |
| USER_VIEW                        | FUNCTIONAL_MANAGER |
| USER_ROLE_GLOBAL_MANAGER         | FUNCTIONAL_MANAGER |
