<?php

declare(strict_types=1);

namespace App\Enums\Authorization;

enum Permission: string
{
    case ADMIN_LOG_ENTRY_VIEW = 'admin_log_entry.view';
    case CORE_ENTITY_CREATE = 'core_entity.create';
    case CORE_ENTITY_DELETE = 'core_entity.delete';
    case CORE_ENTITY_FG_REMARKS = 'core_entity.fg-remarks';
    case CORE_ENTITY_IMPORT = 'import';
    case CORE_ENTITY_UPDATE = 'core_entity.update';
    case CORE_ENTITY_VIEW = 'core_entity.view';
    case DATA_BREACH_RECORD_CREATE = 'data_breach_record.create';
    case DATA_BREACH_RECORD_DELETE = 'data_breach_record.delete';
    case DATA_BREACH_RECORD_UPDATE = 'data_breach_record.update';
    case DATA_BREACH_RECORD_VIEW = 'data_breach_record.view';
    case DOCUMENT_CREATE = 'document.create';
    case DOCUMENT_DELETE = 'document.delete';
    case DOCUMENT_UPDATE = 'document.update';
    case DOCUMENT_VIEW = 'document.view';
    case EXPORT = 'export';
    case LOOKUP_LIST_CREATE = 'lookup_list.create';
    case LOOKUP_LIST_DELETE = 'lookup_list.delete';
    case LOOKUP_LIST_UPDATE = 'lookup_list.update';
    case LOOKUP_LIST_VIEW = 'lookup_list.view';
    case MANAGEMENT_CREATE = 'management.create';
    case MANAGEMENT_DELETE = 'management.delete';
    case MANAGEMENT_UPDATE = 'management.update';
    case MANAGEMENT_VIEW = 'management.view';
    case ORGANISATION_CREATE = 'organisation.create';
    case ORGANISATION_DELETE = 'organisation.delete';
    case ORGANISATION_UPDATE = 'organisation.update';
    case ORGANISATION_UPDATE_IP_WHITELIST = 'organisation.update_ip_whitelist';
    case ORGANISATION_VIEW = 'organisation.view';
    case ORGANISATION_USER_VIEW = 'organisation_user.view';
    case OTP_DISABLE = 'otp_disable';
    case PUBLIC_WEBSITE_UPDATE = 'public_website.edit';
    case RESPONSIBLE_CREATE = 'responsible.create';
    case RESPONSIBLE_DELETE = 'responsible.delete';
    case RESPONSIBLE_UPDATE = 'responsible.update';
    case RESPONSIBLE_VIEW = 'responsible.view';
    case RESPONSIBLE_LEGAL_ENTITY_CREATE = 'responsible_legal_entity.create';
    case RESPONSIBLE_LEGAL_ENTITY_DELETE = 'responsible_legal_entity.delete';
    case RESPONSIBLE_LEGAL_ENTITY_UPDATE = 'responsible_legal_entity.update';
    case RESPONSIBLE_LEGAL_ENTITY_VIEW = 'responsible_legal_entity.view';
    case SNAPSHOT_CREATE = 'snapshot.create';
    case SNAPSHOT_APPROVAL_CREATE = 'snapshot_approval.create';
    case SNAPSHOT_APPROVAL_DELETE = 'snapshot_approval.delete';
    case SNAPSHOT_APPROVAL_ORGANISATION_OVERVIEW = 'snapshot_approval.organisation_overview';
    case SNAPSHOT_APPROVAL_UPDATE_PERSONAL = 'snapshot_approval.update_personal';
    case SNAPSHOT_APPROVAL_VIEW = 'snapshot_approval.view';
    case SNAPSHOT_STATE_TO_APPROVE = 'snapshot_state.to_approve';
    case SNAPSHOT_STATE_TO_ESTABLISHED = 'snapshot_state.to_established';
    case SNAPSHOT_STATE_TO_OBSOLETE = 'snapshot_state.to_obsolete';
    case SNAPSHOT_VIEW = 'snapshot.view';
    case TAG_CREATE = 'tag.create';
    case TAG_DELETE = 'tag.delete';
    case TAG_UPDATE = 'tag.update';
    case TAG_VIEW = 'tag.view';
    case TRANSFER_EXPORT = 'transfer.export';
    case TRANSFER_IMPORT = 'transfer.import';
    case USER_CREATE = 'user.create';
    case USER_DELETE = 'user.delete';
    case USER_IMPORT = 'user.import';
    case USER_UPDATE = 'user.update';
    case USER_VIEW = 'user.view';
    case USER_PROFILE_SETTINGS_MANDATEHOLDER = 'user.profile.settings.mandateholder';
    case USER_ROLE_GLOBAL_MANAGE = 'user_role.global_manage';
    case USER_ROLE_ORGANISATION_CPO_MANAGE = 'user_role.organisation_cpo_manage';
    case USER_ROLE_ORGANISATION_MANAGE = 'user_role.organisation_manage';
}
