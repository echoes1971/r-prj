# -*- coding: utf-8 -*-
from rprj.formulator.qtgui import *

formschema_type_list = {}


class FUser(FMasterDetail):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FMasterDetail.__init__(self, nome, azione, metodo, enctype, dbmgr)
        _mydbe = self.getDBE(dbmgr)
        # Fields
        self.addField('', -1, 'id',
                      FNumber('id', 'ID', '', '', 'formtable'))
        self.addField('', -1, 'login',
                      FString('login', 'Login', 'Inserire la login', 50, 255, '', 'formtable'))
        self.addField('', -1, 'pwd',
                      FPassword('pwd', 'Password', 'Inserire la login', 50, 255, '', 'formtable'))
        self.addField('', -1, 'pwd_salt',
                      FList('pwd_salt', 'Password encrypting', 'Password encrypt saalt', 255, 40, '0', 'formtable',
                            {'': '-', 'md5': 'MD5', 'sha1': 'SHA1'}, 1, False))
        self.addField('', -1, 'fullname',
                      FString('fullname', 'Full Name', 'Inserire la login', 50, 255, '', 'formtable'))
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('group_id')
        self.addField('', -1, 'group_id',
                      FKField('group_id', 'Group', 'Active group.', '-4', 'formtable', _mydbe, _tmpFK, ['name'],
                              'FGroup', 'select', ' - ', 1, False))
        # Details
        self.addDetail("FUserGroupAssociation")
        self.addDetail("FPeople")

    def getDetailIcon(self):
        return "icons/user.png"

    def getDetailTitle(self):
        return 'User'

    def getDetailColumnNames(self):
        return ['login', 'pwd', 'pwd_salt', 'fullname', 'group_id']

    def getFilterForm(self):
        return FUser()

    def getFilterFields(self):
        return ['login', 'fullname', 'group_id']

    def getListTitle(self):
        return 'Users'

    def getListColumnNames(self):
        return ['id', 'login', 'fullname', 'group_id']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBEUser")()
        try:
            return DBEUser()
        except Exception:
            return None

    def getShortDescription(self, dbmgr=None):
        return self.getValue('login')
formschema_type_list["FUser"] = FUser


class FGroup(FMasterDetail):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FMasterDetail.__init__(self, nome, azione, metodo, enctype, dbmgr)
        # Fields
        self.addField('', -1, 'id',
                      FNumber('id', 'ID', '', '', 'formtable'))
        self.addField('', -1, 'name',
                      FString('name', 'Name', 'Nome del gruppo.', 50, 255, '', 'formtable'))
        self.addField('', -1, 'description',
                      FTextArea('description', 'Description', 'Descrizione del gruppo.', 255, '', 'formtable', 50, 5,
                                True))
        # Details
        self.addDetail("FUserGroupAssociation")

    def getDetailIcon(self):
        return "icons/group_16x16.gif"

    def getDetailTitle(self):
        return 'Group'

    def getDetailColumnNames(self):
        return ['name', 'description']

    def getFilterForm(self):
        return FGroup()

    def getFilterFields(self):
        return ['name', 'description']

    def getListTitle(self):
        return 'Groups'

    def getListColumnNames(self):
        return ['id', 'name', 'description']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBEGroup")()
        try:
            return DBEGroup()
        except Exception:
            return None

    def getShortDescription(self, dbmgr=None):
        return self.getValue('name')
formschema_type_list["FGroup"] = FGroup


class FUserGroupAssociation(FAssociation):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FAssociation.__init__(self, nome, azione, metodo, enctype, dbmgr)

    def getDetailTitle(self):
        return 'Association User-Group'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBEUserGroup")()
        try:
            return DBEUserGroup()
        except Exception:
            return None

    def getShortDescription(self, dbmgr=None):
        raise Exception("TODO")
formschema_type_list["FUserGroupAssociation"] = FUserGroupAssociation


class FLog(FMasterDetail):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FMasterDetail.__init__(self, nome, azione, metodo, enctype, dbmgr)
        # _mydbe = self.getDBE(dbmgr)
        # Fields
        self.addField('', -1, 'ip',
                      FString('ip', 'IP', '', 16, 16, '', 'formtable'))
        self.addField('', -1, 'data',
                      FDateTime('data', 'Date', '', '2012-01-29', 'formtable', True, False))
        self.addField('', -1, 'ora',
                      FDateTime('ora', 'Hour', '', dbmgr.getTodayString(), 'formtable', False, True))
        self.addField('', -1, 'count',
                      FNumber('count', 'Count', '', '', 'formtable'))
        self.addField('', -1, 'url',
                      FString('url', 'URL', '', 50, 255, '', 'formtable'))
        self.addField('', -1, 'note',
                      FString('note', 'Note', '', 50, 255, '', 'formtable'))
        self.addField('', -1, 'note2',
                      FTextArea('note2', 'Note 2', '', 255, '', 'formtable', 50, 5, True))

    def getDetailIcon(self):
        return "icons/text-x-log.png"

    def getDetailTitle(self):
        return 'Log'

    def getDetailColumnNames(self):
        return ['data', 'ora', 'count', 'url', 'note', 'note2']

    def getFilterFields(self):
        return ['ip', 'data', 'count', 'url', 'note']

    def getListTitle(self):
        return 'Log'

    def getListColumnNames(self):
        return ['data', 'ora', 'count', 'ip', 'url', 'note']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBELog")()
        try:
            return DBELog()
        except Exception:
            return None

    def getShortDescription(self, dbmgr=None):
        return "%s - %s" % (self.getValue('ip'), self.getValue('url'))
formschema_type_list["FLog"] = FLog


class FLogFilter(FLog):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FLog.__init__(self, nome, azione, metodo, enctype, dbmgr)
        # Fields
        self.addField('', -1, 'from_ip',
                      FString('from_ip', 'IP >=', '', 16, 16, '', 'formtable'))
        self.addField('', -1, 'to_ip',
                      FString('to_ip', 'IP <=', '', 16, 16, '', 'formtable'))
        self.addField('', -1, 'from_data',
                      FDateTime('from_data', 'Date >=', 'Date from', '2012-01-29', 'formtable', True, False))
        self.addField('', -1, 'to_data',
                      FDateTime('to_data', 'Date <=', 'Date to', '2012-01-29', 'formtable', True, False))

    def getDetailIcon(self):
        return "icons/text-x-log.png"

    def getDetailTitle(self):
        return 'Log Filter'

    def getDetailColumnNames(self):
        return ['ora', 'count', 'url', 'note', 'note2']

    def getFilterFields(self):
        return ['from_ip', 'to_ip', 'from_data', 'to_data', 'url', 'note']

    def getListTitle(self):
        return 'Log'

    def getListColumnNames(self):
        return ['data', 'ora', 'count', 'ip', 'url', 'note']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBELog")()
        try:
            return DBELog()
        except Exception:
            return None
formschema_type_list["FLogFilter"] = FLogFilter


class FObject(FMasterDetail):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FMasterDetail.__init__(self, nome, azione, metodo, enctype, dbmgr)
        _mydbe = self.getDBE(dbmgr)
        # Fields
        self.addField('', -1, 'id',
                      FUuid('id', 'ID', '', 16, 0, 'formtable', ''))
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('creator')
        self.addField('', -1, 'creator',
                      FKField('creator', 'Created by', 'Creatore', '', 'formtable', _mydbe, _tmpFK, ['login'], 'FUser',
                              'readonly', ' - ', 1, False))
        self.addField('', -1, 'creation_date',
                      FDateTimeReadOnly('creation_date', 'Created on', 'Creato il', dbmgr.getTodayString(), 'formtable',
                                        True, True))
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('last_modify')
        self.addField('', -1, 'last_modify',
                      FKField('last_modify', 'Modified by', 'Modificato da', '', 'formtable', _mydbe, _tmpFK, ['login'],
                              'FUser', 'readonly', ' - ', 1, False))
        self.addField('', -1, 'last_modify_date',
                      FDateTimeReadOnly('last_modify_date', 'Modified on', 'Ultima modifica', dbmgr.getTodayString(),
                                        'formtable', True, True))
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('deleted_by')
        self.addField('', -1, 'deleted_by',
                      FKField('deleted_by', 'Deleted by', 'Deleted by', '', 'formtable', _mydbe, _tmpFK, ['login'],
                              'FUser', 'readonly', ' - ', 1, False))
        self.addField('', -1, 'deleted_date',
                      FDateTimeReadOnly('deleted_date', 'Deleted on', 'Deleted on', '0000-00-00 00:00:00', 'formtable',
                                        True, True))
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('father_id')
        self.addField('', -1, 'father_id',
                      FKObjectField('father_id', 'Parent', 'Padre', '', 'formtable', _mydbe, _tmpFK, ['name'], '',
                                    'select', ' - ', 1, False))
        self.addField('', -1, 'name',
                      FString('name', 'Name', 'Nome del gruppo.', 50, 255, '', 'formtable'))
        self.addField('', -1, 'description',
                      FTextArea('description', 'Description', 'Descrizione del gruppo.', 255, '', 'formtable', -1, -1,
                                True))
        #FTextArea('description', 'Description', 'Descrizione del gruppo.',255,'', 'formtable',
        #          50,5,True))
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('owner')
        self.addField('_permission', -1, 'owner',
                      FKField('owner', 'Owner', 'Proprietario.', '', 'formtable', _mydbe, _tmpFK, ['login'], 'FUser',
                              'select', ' - ', 1, False))
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('group_id')
        self.addField('_permission', -1, 'group_id',
                      FKField('group_id', 'Group', 'Active group.', '', 'formtable', _mydbe, _tmpFK, ['name'], 'FGroup',
                              'select', ' - ', 1, False))
        self.addField('_permission', -1, 'permissions',
                      FPermissions('permissions', 'Permissions', 'Unix style', 9, 9, 'rwx------', 'formtable'))

    def getDetailTitle(self):
        return 'Object'

    def getDetailColumnNames(self):
        return [
            'creator', 'creation_date', 'last_modify', 'last_modify_date', 'deleted_by',
            'deleted_date',
            'father_id',
            'name', 'description', 'owner', 'group_id', 'permissions',
            ]

    def getDetailReadOnlyColumnNames(self):
        return ['id', 'creator', 'creation_date', 'last_modify', 'last_modify_date', 'deleted_by', 'deleted_date']

    def getFilterFields(self):
        return ['name', 'description', 'owner', 'group_id']

    def getListTitle(self):
        return 'Objects'

    def getDecodeGroupNames(self):
        ret = FMasterDetail.getDecodeGroupNames(self)
        #ret[''] = 'Info'
        ret['_permission'] = 'Permissions'
        return ret

    def getGroupNames(self):
        ret = [k for k in FMasterDetail.getGroupNames(self) if k not in ['', '_permission']]
        ret.append('')
        ret.append('_permission')
        return ret

    def getListColumnNames(self):
        return ['id', 'name', 'description']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBEObject")()
        try:
            return DBEObject()
        except Exception:
            return None

    def getShortDescription(self, dbmgr=None):
        return self.getValue('name')
formschema_type_list["FObject"] = FObject


class FCompany(FObject):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FObject.__init__(self, nome, azione, metodo, enctype, dbmgr)
        _mydbe = self.getDBE(dbmgr)
        # Fields
        self.addField('contact', -1, 'p_iva',
                      FString('p_iva', 'P.IVA', 'Partita IVA', 50, 255, '', 'formtable'))
        self.addField('phone', -1, 'phone',
                      FString('phone', 'Phone', 'Telefono', 50, 255, '', 'formtable'))
        self.addField('phone', -1, 'fax',
                      FString('fax', 'Fax', 'Fax', 50, 255, '', 'formtable'))
        self.addField('web', -1, 'email',
                      FString('email', 'Email', 'Email', 50, 255, '', 'formtable'))
        self.addField('web', -1, 'url',
                      FString('url', 'URL', 'Sito web', 50, 255, '', 'formtable'))
        self.addField('address', -1, 'street',
                      FString('street', 'Street', 'Via', 50, 255, '', 'formtable'))
        self.addField('address', -1, 'zip',
                      FString('zip', 'ZIP', 'CAP', 10, 255, '', 'formtable'))
        self.addField('address', -1, 'city',
                      FString('city', 'City', 'Città.', 50, 255, '', 'formtable'))
        self.addField('address', -1, 'state',
                      FString('state', 'State', 'Provincia.', 20, 255, '', 'formtable'))
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('fk_countrylist_id')
        self.addField('address', -1, 'fk_countrylist_id',
                      FKField('fk_countrylist_id', 'Country', 'Nazione.', '82', 'formtable', _mydbe, _tmpFK,
                              ['Common_Name'], '', 'select', ' - ', 1, False))
        # Details
        self.addDetail("FFolder")
        self.addDetail("FProjectCompany")
        self.addDetail("FPeople")
        self.addDetail("FPage")
        self.addDetail("FLink")
        self.addDetail("FNote")
        # Beautifying
        self.removeFromGroup('name', '')
        self.removeFromGroup('description', '')
        self.addToGroup('name', 'contact', 0)
        self.addToGroup('description', 'contact')
        myfield = self.getField('description')
        myfield.height = 5

    def getDetailIcon(self):
        return "icons/company_16x16.gif"

    def getDetailTitle(self):
        return 'Company'

    def getDetailColumnNames(self):
        ret = FObject.getDetailColumnNames(self)
        for x in ["p_iva", "street", "zip", "city", "state", "fk_countrylist_id", "phone", "fax", "email", "url"]:
            ret.append(x)
        return ret

    def getFilterForm(self):
        return FCompany()

    def getFilterFields(self):
        return ['name', 'p_iva', 'zip', 'city', 'fk_countrylist_id']

    def getListTitle(self):
        return 'Companies'

    def getGroupNames(self):
        return ['contact', 'address', 'phone', 'web', '', '_permission']

    def getDecodeGroupNames(self):
        ret = FObject.getDecodeGroupNames(self)
        ret[''] = 'Details'
        ret['contact'] = 'Company'
        ret['phone'] = 'Phone'
        ret['address'] = 'Address'
        ret['web'] = 'Web'
        return ret

    def getListColumnNames(self):
        return ['name', 'phone', 'city', 'fk_countrylist_id']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBECompany")()
        try:
            return DBECompany()
        except Exception:
            return None
formschema_type_list["FCompany"] = FCompany


class FPeople(FObject):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FObject.__init__(self, nome, azione, metodo, enctype, dbmgr)
        _mydbe = self.getDBE(dbmgr)
        # Fields
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('fk_users_id')
        self.addField('contact', -1, 'fk_users_id',
                      FKField('fk_users_id', 'User', 'Utente.', '', 'formtable', _mydbe, _tmpFK, ['login'], 'FUser',
                              'select', ' - ', 1, False))
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('fk_companies_id')
        self.addField('contact', -1, 'fk_companies_id',
                      FKField('fk_companies_id', 'Company', 'Organizzazione.', '', 'formtable', _mydbe, _tmpFK,
                              ['name', 'state'], 'FCompany', 'select', ' - ', 1, False))
        self.addField('contact', -1, 'codice_fiscale',
                      FString('codice_fiscale', 'Codice Fiscale', 'Codice Fiscale', 50, 255, '', 'formtable'))
        self.addField('contact', -1, 'p_iva',
                      FString('p_iva', 'P.IVA', 'Partita IVA', 50, 255, '', 'formtable'))
        self.addField('phone', -1, 'office_phone',
                      FString('office_phone', 'Office Phone', 'Telefono Ufficio', 50, 255, '', 'formtable'))
        self.addField('phone', -1, 'mobile',
                      FString('mobile', 'Mobile', 'Cellulare', 50, 255, '', 'formtable'))
        self.addField('phone', -1, 'phone',
                      FString('phone', 'Phone', 'Telefono', 50, 255, '', 'formtable'))
        self.addField('phone', -1, 'fax',
                      FString('fax', 'Fax', 'Fax', 50, 255, '', 'formtable'))
        self.addField('address', -1, 'street',
                      FString('street', 'Street', 'Via', 50, 255, '', 'formtable'))
        self.addField('address', -1, 'zip',
                      FString('zip', 'ZIP', 'CAP', 10, 255, '', 'formtable'))
        self.addField('address', -1, 'city',
                      FString('city', 'City', 'Città.', 50, 255, '', 'formtable'))
        self.addField('address', -1, 'state',
                      FString('state', 'State', 'Provincia.', 20, 255, '', 'formtable'))
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('fk_countrylist_id')
        self.addField('address', -1, 'fk_countrylist_id',
                      FKField('fk_countrylist_id', 'Country', 'Nazione.', '82', 'formtable', _mydbe, _tmpFK,
                              ['Common_Name'], '', 'select', ' - ', 1, False))
        self.addField('web', -1, 'email',
                      FString('email', 'Email', 'Email', 50, 255, '', 'formtable'))
        self.addField('web', -1, 'url',
                      FString('url', 'URL', 'Sito web', 50, 255, '', 'formtable'))
        # Details
        self.addDetail("FFolder")
        self.addDetail("FProjectPeople")
        self.addDetail("FPage")
        self.addDetail("FLink")
        self.addDetail("FNote")
        # Beautifying
        self.removeFromGroup('name', '')
        self.removeFromGroup('description', '')
        self.addToGroup('name', 'contact', 0)
        self.addToGroup('description', 'contact')
        myfield = self.getField('description')
        myfield.height = 5

    def getDetailIcon(self):
        return "icons/people.png"

    def getDetailTitle(self):
        return 'Person'

    def getDetailColumnNames(self):
        return ['name', 'fk_users_id', 'fk_companies_id', 'codice_fiscale', 'p_iva', 'street', 'zip', 'city', 'state',
                'fk_countrylist_id', 'phone', 'office_phone', 'mobile', 'fax', 'email', 'url', 'owner', 'group_id',
                'permissions']

    def getDetailReadOnlyColumnNames(self):
        return ['creator', 'creation_date', 'last_modify', 'last_modify_date']

    def getFilterForm(self):
        return FPeople()

    def getFilterFields(self):
        return ['name', 'zip', 'city', 'fk_countrylist_id', 'fk_companies_id']

    def getListTitle(self):
        return 'People'

    def getGroupNames(self):
        return ['contact', 'address', 'phone', 'web', '', '_permission']

    def getDecodeGroupNames(self):
        ret = FObject.getDecodeGroupNames(self)
        ret[''] = 'Details'
        ret['contact'] = 'Person'
        ret['phone'] = 'Phone'
        ret['address'] = 'Address'
        ret['web'] = 'Web'
        return ret

    def getListColumnNames(self):
        return ['name', 'phone', 'email', 'fk_companies_id']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBEPeople")()
        try:
            return DBEPeople()
        except Exception:
            return None
formschema_type_list["FPeople"] = FPeople


class FEvent(FObject):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FObject.__init__(self, nome, azione, metodo, enctype, dbmgr)
        _mydbe = self.getDBE(dbmgr)
        # Fields
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('fk_obj_id')
        self.addField('', -1, 'fk_obj_id',
                      FKObjectField('fk_obj_id', 'Linked to', 'Collegata a', '', 'formtable', _mydbe, _tmpFK, ['name'],
                                    '', 'select', ' - ', 1, False))
        self.addField('event', -1, 'start_date',
                      FDateTime('start_date', 'Start', 'Start', dbmgr.getTodayString(), 'formtable', True, True))
        self.addField('event', -1, 'end_date',
                      FDateTime('end_date', 'End', 'End', dbmgr.getTodayString(), 'formtable', True, True))
        self.addField('event', -1, 'all_day',
                      FList('all_day', 'All day', 'All day event', 255, 40, '0', 'formtable', {'0': 'No', '1': 'Yes'},
                            1, False))
        self.addField('event', -1, 'url',
                      FString('url', 'Url', 'Url', 50, 255, '', 'formtable'))
        self.addField('event', -1, 'category',
                      FString('category', 'Category', 'Category', 50, 255, '', 'formtable'))
        # Details
        self.addDetail("FPeople")
        self.addDetail("FLink")
        self.addDetail("FFile")
        # Beautifying
        self.removeFromGroup('name', '')
        self.removeFromGroup('description', '')
        self.addToGroup('name', 'event', 3)
        self.addToGroup('description', 'event', 4)

    def getDetailIcon(self):
        return "icons/event_16x16.png"

    def getDetailTitle(self):
        return 'Event'

    def getDetailColumnNames(self):
        ret = FObject.getDetailColumnNames(self)
        for x in ["fk_obj_id", "start_date", "end_date", "all_day", "url", "category", "alarm", "alarm_unit",
                  "alarm_minute", "before_event", "recurrence", "recurrence_type"]:
            ret.append(x)
        return ret

    def getFilterFields(self):
        return ['name', 'father_id']

    def getListTitle(self):
        return 'Events'

    def getDecodeGroupNames(self):
        ret = FObject.getDecodeGroupNames(self)
        ret[''] = 'Details'
        ret['event'] = 'Event'
        ret['recurrence'] = 'Recurrence'
        ret['alarm'] = 'Alarm'
        return ret

    def getListColumnNames(self):
        return ['start_date', 'end_date', 'name', 'father_id']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBEEvent")()
        try:
            return DBEEvent()
        except Exception:
            return None
formschema_type_list["FEvent"] = FEvent


class FEventFilter(FEvent):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FEvent.__init__(self, nome, azione, metodo, enctype, dbmgr)
        # Fields
        self.addField('', -1, 'from_start_date',
                      FDateTime('from_start_date', 'Date >=', 'Date from', '2012-01-29', 'formtable', True, False))
        self.addField('', -1, 'to_start_date',
                      FDateTime('to_start_date', 'Date <=', 'Date to', '', 'formtable', True, False))
        # Details
        self.addDetail("FPeople")
        self.addDetail("FLink")
        self.addDetail("FFile")

    def getDetailIcon(self):
        return "icons/event_16x16.png"

    def getDetailTitle(self):
        return 'Event Filter'

    def getDetailColumnNames(self):
        return ['creation_date', 'creator', 'father_id', 'name', 'description', 'fk_obj_id', 'owner', 'group_id',
                'permissions', 'start_date', 'end_date', 'all_day', 'url', 'category', 'alarm', 'alarm_unit',
                'alarm_minute', 'before_event', 'recurrence', 'recurrence_type']

    def getDetailReadOnlyColumnNames(self):
        return ['creator', 'creation_date', 'last_modify', 'last_modify_date']

    def getFilterFields(self):
        return ['name', 'from_start_date', 'to_start_date']

    def getListTitle(self):
        return 'Events'

    def getListColumnNames(self):
        return ['start_date', 'end_date', 'name', 'father_id']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBEEvent")()
        try:
            return DBEEvent()
        except Exception:
            return None
# FIXME formschema_type_list["FEventFilter"]=FEventFilter


class FFile(FObject):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FObject.__init__(self, nome, azione, metodo, enctype, dbmgr)
        _mydbe = self.getDBE(dbmgr)
        # Fields
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('fk_obj_id')
        self.addField('', -1, 'fk_obj_id',
                      FKObjectField('fk_obj_id', 'Linked to', 'Collegata a', '', 'formtable', _mydbe, _tmpFK, ['name'],
                                    '', 'select', ' - ', 1, False))
        self.addField('file', -1, 'path',
                      FString('path', 'Path', 'Percorso', 50, 255, '', 'formtable'))
        self.addField('file', -1, 'filename',
                      FFileField('filename', 'File name', 'Nome del file', 20, 255, '', 'formtable'))
        self.addField('file', -1, 'checksum',
                      FString('checksum', 'Checksum', 'Checksum SHA1', 40, 40, '', 'formtable'))
        self.addField('file', -1, 'mime',
                      FString('mime', 'Mime type', 'Mime type', 40, 40, '', 'formtable'))
        self.addField('file', -1, 'alt_link',
                      FString('alt_link', 'Alternative Link', 'Link alternativo', 50, 255, '', 'formtable'))
        self.removeFromGroup('name', '')
        self.removeFromGroup('description', '')
        self.addToGroup('name', 'file', 0)
        self.addToGroup('description', 'file', 1)

    def getDetailIcon(self):
        return "icons/file_16x16.gif"

    def getDetailTitle(self):
        return 'File'

    def getDetailColumnNames(self):
        ret = FObject.getDetailColumnNames(self)
        for x in ["path", "filename", "checksum", "mime", "alt_link"]:
            ret.append(x)
        return ret

    def getDetailReadOnlyColumnNames(self):
        ret = FObject.getDetailReadOnlyColumnNames(self)
        for x in ["checksum", "mime"]:
            ret.append(x)
        return ret

    def getFilterForm(self):
        return FFile()

    def getFilterFields(self):
        return ['father_id', 'name', 'description']

    def getListTitle(self):
        return 'Files'

    def getDecodeGroupNames(self):
        ret = FObject.getDecodeGroupNames(self)
        ret[''] = 'Details'
        ret['file'] = 'File'
        return ret

    def getListColumnNames(self):
        return ['name', 'father_id', 'mime', 'path', 'filename']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBEFile")()
        try:
            return DBEFile()
        except Exception:
            return None

    def getActions(self):
        ret = FObject.getActions(self)
        ret['download'] = ['Download', '_do.php', 'icons/download.png', 'Download']
        return ret

    def isImage(self):
        _mime = self.getValue('mime')
        return _mime > '' and _mime[0:5] == 'image'
formschema_type_list["FFile"] = FFile


class FFolder(FObject):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FObject.__init__(self, nome, azione, metodo, enctype, dbmgr)
        _mydbe = self.getDBE(dbmgr)
        # Fields
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('fk_obj_id')
        self.addField('', -1, 'fk_obj_id',
                      FKObjectField('fk_obj_id', 'Linked to', 'Collegata a', '', 'formtable', _mydbe, _tmpFK, ['name'],
                                    '', 'select', ' - ', 1, False))
        self.addField('folder', -1, 'childs_sort_order',
                      FChildSort('childs_sort_order', 'Sort order', 'Ordinamento dei figli diretti', 255, 40, '',
                                 'formtable', {}, 10, True))
        # Details
        self.addDetail("FFolder")
        self.addDetail("FPage")
        self.addDetail("FNews")
        self.addDetail("FLink")
        self.addDetail("FNote")
        self.addDetail("FEvent")
        self.addDetail("FFile")
        self.addDetail("FMail")
        self.addDetail("FTodo")
        self.addDetail("FTimetrack")
        self.removeFromGroup('name', '')
        self.removeFromGroup('description', '')
        self.addToGroup('name', 'folder', 0)
        self.addToGroup('description', 'folder', 1)

    def getDetailIcon(self):
        return "icons/folder_16x16.gif"

    def getDetailTitle(self):
        return 'Folder'

    def getDetailColumnNames(self):
        ret = FObject.getDetailColumnNames(self)
        for x in ["fk_obj_id", "childs_sort_order"]:
            ret.append(x)
        return ret

    def getDecodeGroupNames(self):
        ret = FObject.getDecodeGroupNames(self)
        ret[''] = 'Details'
        ret['folder'] = 'Folder'
        return ret

    def getFilterForm(self):
        return FFolder()

    def getFilterFields(self):
        return ['name', 'fk_obj_id', 'father_id']

    def getListTitle(self):
        return 'Folders'

    def getListColumnNames(self):
        return ['name', 'description', 'father_id', 'fk_obj_id']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBEFolder")()
        try:
            return DBEFolder()
        except Exception:
            return None
formschema_type_list["FFolder"] = FFolder


class FLink(FObject):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FObject.__init__(self, nome, azione, metodo, enctype, dbmgr)
        _mydbe = self.getDBE(dbmgr)
        # Fields
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('fk_obj_id')
        self.addField('', -1, 'fk_obj_id',
                      FKObjectField('fk_obj_id', 'Linked to', 'Collegata a', '', 'formtable', _mydbe, _tmpFK, ['name'],
                                    '', 'select', ' - ', 1, False))
        self.addField('link', -1, 'href',
                      FString('href', 'Href', 'Url destinazione', 50, 255, '', 'formtable'))
        self.addField('link', -1, 'target',
                      FString('target', 'Target', 'Frame target.', 50, 255, '', 'formtable'))
        # Beautifying
        self.removeFromGroup('name', '')
        self.removeFromGroup('description', '')
        self.addToGroup('name', 'link', 0)
        self.addToGroup('description', 'link')
        myfield = self.getField('description')
        myfield.height = 5

    def getDetailIcon(self):
        return "icons/link_16x16.gif"

    def getDetailTitle(self):
        return 'Link'

    def getDetailColumnNames(self):
        ret = FObject.getDetailColumnNames(self)
        for x in ["href", "target", "fk_obj_id"]:
            ret.append(x)
        return ret

    def getDecodeGroupNames(self):
        ret = FObject.getDecodeGroupNames(self)
        ret[''] = 'Details'
        ret['link'] = 'Link'
        return ret

    def getFilterForm(self):
        return FLink()

    def getFilterFields(self):
        return ['fk_obj_id', 'name', 'href']

    def getListTitle(self):
        return 'Links'

    def getListColumnNames(self):
        return ['creation_date', 'fk_obj_id', 'name', 'href']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBELink")()
        try:
            return DBELink()
        except Exception:
            return None
formschema_type_list["FLink"] = FLink


class FNote(FObject):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FObject.__init__(self, nome, azione, metodo, enctype, dbmgr)
        _mydbe = self.getDBE(dbmgr)
        # Fields
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('fk_obj_id')
        self.addField('', -1, 'fk_obj_id',
                      FKObjectField('fk_obj_id', 'Linked to', 'Collegata a', '', 'formtable', _mydbe, _tmpFK, ['name'],
                                    '', 'select', ' - ', 1, False))
        # Beautifying
        self.removeFromGroup('name', '')
        self.removeFromGroup('description', '')
        self.addToGroup('name', 'note', 0)
        self.addToGroup('description', 'note')

    def getDetailIcon(self):
        return "icons/note_16x16.gif"

    def getDetailTitle(self):
        return 'Note'

    def getDetailColumnNames(self):
        ret = FObject.getDetailColumnNames(self)
        for x in ["fk_obj_id"]:
            ret.append(x)
        return ret

    def getDecodeGroupNames(self):
        ret = FObject.getDecodeGroupNames(self)
        ret[''] = 'Details'
        ret['note'] = 'Note'
        return ret

    def getFilterForm(self):
        return FNote()

    def getFilterFields(self):
        return ['name', 'fk_obj_id']

    def getListTitle(self):
        return 'Notes'

    def getListColumnNames(self):
        return ['creation_date', 'name', 'fk_obj_id']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBENote")()
        try:
            return DBENote()
        except Exception:
            return None
formschema_type_list["FNote"] = FNote


class FPage(FObject):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FObject.__init__(self, nome, azione, metodo, enctype, dbmgr)
        _mydbe = self.getDBE(dbmgr)
        # Fields
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('fk_obj_id')
        self.addField('', -1, 'fk_obj_id',
                      FKObjectField('fk_obj_id', 'Linked to', 'Collegata a', '', 'formtable', _mydbe, _tmpFK, ['name'],
                                    '', 'select', ' - ', 1, False))
        self.addField('html', -1, 'html',
                      FHtml('html', 'Html', 'Contenuto html.', 255, '', 'formtable', 50, 50, True))
        self.addField('html', -1, 'language',
                      FLanguage('language', 'Language', 'Page language', 50, 255, 'en_US', 'formtable'))
        # Details
        self.addDetail("FPage")
        self.addDetail("FLink")
        self.addDetail("FFile")
        self.addDetail("FEvent")
        self.removeFromGroup('name', '')
        self.removeFromGroup('description', '')
        self.addToGroup('name', 'html', 0)
        self.addToGroup('description', 'html', 1)
        myfield = self.getField('description')
        myfield.height = 2

    def getDetailIcon(self):
        return "icons/page_16x16.gif"

    def getDetailTitle(self):
        return 'Page'

    def getDetailColumnNames(self):
        ret = FObject.getDetailColumnNames(self)
        for x in ["language", "html", "fk_obj_id"]:
            ret.append(x)
        return ret
    #def getDetailReadOnlyColumnNames(self):
        # return ['creator', 'creation_date', 'last_modify', 'last_modify_date', 'deleted_by',
        #         'deleted_date',]

    def getFilterForm(self):
        return FPage()

    def getFilterFields(self):
        return ['name', 'fk_obj_id', 'father_id']

    def getListTitle(self):
        return 'Pages'

    def getDecodeGroupNames(self):
        ret = FObject.getDecodeGroupNames(self)
        ret[''] = 'Details'
        ret['html'] = 'Page'
        return ret

    def getListColumnNames(self):
        return ['creation_date', 'name', 'fk_obj_id']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBEPage")()
        try:
            return DBEPage()
        except Exception:
            return None
formschema_type_list["FPage"] = FPage


class FNews(FPage):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FPage.__init__(self, nome, azione, metodo, enctype, dbmgr)
        # Details
        self.addDetail("FLink")
        self.addDetail("FFile")
        self.addDetail("FEvent")
        self.addDetail("FNews")

    def getDetailIcon(self):
        return "icons/news.png"

    def getDetailTitle(self):
        return 'News'

    def getDecodeGroupNames(self):
        ret = FPage.getDecodeGroupNames(self)
        ret['html'] = 'News'
        return ret

    def getFilterForm(self):
        return FNews()

    def getFilterFields(self):
        return ['name', 'fk_obj_id', 'father_id']

    def getListTitle(self):
        return 'News'

    def getListColumnNames(self):
        return ['creation_date', 'name', 'fk_obj_id']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBENews")()
        try:
            return DBENews()
        except Exception:
            return None
formschema_type_list["FNews"] = FNews


# #### Projects: start.

class FProject(FObject):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FObject.__init__(self, nome, azione, metodo, enctype, dbmgr)
        # Details
        self.addDetail("FFolder")
        self.addDetail("FProjectProject")
        self.addDetail("FProjectCompany")
        self.addDetail("FProjectPeople")
        self.addDetail("FTodo")
        self.addDetail("FTimetrack")
        self.addDetail("FPage")
        self.addDetail("FLink")
        self.addDetail("FNote")
        self.addDetail("FEvent")
        # Beautifying
        self.removeFromGroup('name', '')
        self.removeFromGroup('description', '')
        self.addToGroup('name', 'project', 0)
        self.addToGroup('description', 'project')

    def getDetailIcon(self):
        return "icons/project_16x16.gif"

    def getDetailTitle(self):
        return 'Project'

    def getDecodeGroupNames(self):
        ret = FObject.getDecodeGroupNames(self)
        ret[''] = 'Details'
        ret['project'] = 'Project'
        return ret

    def getListTitle(self):
        return 'Projects'

    def getListColumnNames(self):
        return ['name', 'description']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBEProject")()
        try:
            return DBEProject()
        except Exception:
            return None
formschema_type_list["FProject"] = FProject


class FProjectCompany(FAssociation):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FAssociation.__init__(self, nome, azione, metodo, enctype, dbmgr)
        _mydbe = self.getDBE(dbmgr)
        # Fields
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('projects_companies_role_id')
        self.addField('', -1, 'projects_companies_role_id',
                      FKField('projects_companies_role_id', 'Role', 'Ruolo.', '', 'formtable', _mydbe, _tmpFK,
                              ['projects_companies_role_id'], 'FCompany', 'select', ' - ', 1, False))

    def getDetailTitle(self):
        return 'Association Project-Company'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBEProjectCompany")()
        try:
            return DBEProjectCompany()
        except Exception:
            return None

    def getShortDescription(self, dbmgr=None):
        raise Exception("TODO")
formschema_type_list["FProjectCompany"] = FProjectCompany


class FProjectCompanyRole(FObject):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FObject.__init__(self, nome, azione, metodo, enctype, dbmgr)
        # Fields
        self.addField('', -1, 'order_position',
                      FString('order_position', 'Order', 'Ordine', 50, 255, '', 'formtable'))

    def getDetailTitle(self):
        return 'Project-Company Role'

    def getDetailColumnNames(self):
        return ['name', 'description', 'order_position']

    def getDetailReadOnlyColumnNames(self):
        return ['creator', 'creation_date', 'last_modify', 'last_modify_date']

    def getListTitle(self):
        return 'Project-Company Roles'

    def getListColumnNames(self):
        return ['order_position', 'name', 'description']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBEProjectCompanyRole")()
        try:
            return DBEProjectCompanyRole()
        except Exception:
            return None
formschema_type_list["FProjectCompanyRole"] = FProjectCompanyRole


class FProjectPeople(FAssociation):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FAssociation.__init__(self, nome, azione, metodo, enctype, dbmgr)
        _mydbe = self.getDBE(dbmgr)
        # Fields
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('projects_people_role_id')
        self.addField('', -1, 'projects_people_role_id',
                      FKField('projects_people_role_id', 'Role', 'Ruolo.', '', 'formtable', _mydbe, _tmpFK,
                              ['projects_people_role_id'], 'FPeople', 'select', ' - ', 1, False))

    def getDetailTitle(self):
        return 'Association Project-People'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBEProjectPeople")()
        try:
            return DBEProjectPeople()
        except Exception:
            return None

    def getShortDescription(self, dbmgr=None):
        raise Exception("TODO")
formschema_type_list["FProjectPeople"] = FProjectPeople


class FProjectPeopleRole(FObject):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FObject.__init__(self, nome, azione, metodo, enctype, dbmgr)
        # Fields
        self.addField('', -1, 'order_position',
                      FString('order_position', 'Order', 'Ordine', 50, 255, '', 'formtable'))

    def getDetailTitle(self):
        return 'Project-People Role'

    def getDetailColumnNames(self):
        return ['name', 'description', 'order_position']

    def getDetailReadOnlyColumnNames(self):
        return ['creator', 'creation_date', 'last_modify', 'last_modify_date']

    def getListTitle(self):
        return 'Project-People Roles'

    def getListColumnNames(self):
        return ['order_position', 'name', 'description']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBEProjectPeopleRole")()
        try:
            return DBEProjectPeopleRole()
        except Exception:
            return None
formschema_type_list["FProjectPeopleRole"] = FProjectPeopleRole


class FProjectProject(FAssociation):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FAssociation.__init__(self, nome, azione, metodo, enctype, dbmgr)
        _mydbe = self.getDBE(dbmgr)
        # Fields
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('projects_projects_role_id')
        self.addField('', -1, 'projects_projects_role_id',
                      FKField('projects_projects_role_id', 'Role', 'Ruolo.', '', 'formtable', _mydbe, _tmpFK,
                              ['projects_projects_role_id'], 'FProject', 'select', ' - ', 1, False))

    def getDetailTitle(self):
        return 'Association Project-Project'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBEProjectProject")()
        try:
            return DBEProjectProject()
        except Exception:
            return None

    def getShortDescription(self, dbmgr=None):
        raise Exception("TODO")
formschema_type_list["FProjectProject"] = FProjectProject


class FTimetrack(FObject):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FObject.__init__(self, nome, azione, metodo, enctype, dbmgr)
        _mydbe = self.getDBE(dbmgr)
        # Fields
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('fk_obj_id')
        self.addField('', -1, 'fk_obj_id',
                      FKObjectField('fk_obj_id', 'Linked to', 'Collegata a', '', 'formtable', _mydbe, _tmpFK, ['name'],
                                    '', 'select', ' - ', 1, False))
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('fk_progetto')
        self.addField('', -1, 'fk_progetto',
                      FKObjectField('fk_progetto', 'Project', 'Progetto', '', 'formtable', _mydbe, _tmpFK, ['name'],
                                    '', 'select', ' - ', 1, False))
        self.addField('timetrack', -1, 'dalle_ore',
                      FDateTime('dalle_ore', 'From', 'Dalle Ore', dbmgr.getTodayString(), 'formtable', True, True))
        self.addField('timetrack', -1, 'alle_ore',
                      FDateTime('alle_ore', 'To', 'Alle Ore', '', 'formtable', True, True))
        #FDateTime('alle_ore', 'To', 'Alle Ore', dbmgr.getTodayString(), 'formtable',True,True))
        self.addField('timetrack', -1, 'ore_intervento',
                      FDateTime('ore_intervento', 'Hours of Work', 'Ore Intervento', '', 'formtable', False, True))
        self.addField('timetrack', -1, 'ore_viaggio',
                      FDateTime('ore_viaggio', 'Hours of Travel', 'Ore Viaggio', '', 'formtable', False, True))
        self.addField('timetrack', -1, 'km_viaggio',
                      FNumber('km_viaggio', 'KM Travels', 'KM Viaggio', '', 'formtable'))
        self.addField('timetrack', -1, 'luogo_di_intervento',
                      FList('luogo_di_intervento', 'Luogo di intervento', 'Luogo di intervento', 255, 40, '0',
                            'formtable', {'': '', '0': 'Office', '1': 'From office (via ssh/telnet/ecc.)',
                                          '2': 'On site'}, 1, False))
        self.addField('timetrack', -1, 'stato',
                      FList('stato', 'State', 'Stato', 255, 40, '0', 'formtable',
                            {'': '', '0': 'da fatturare', '1': 'non fatturare', '2': 'fatturato', '3': 'assistenza'},
                            1, False))
        self.addField('timetrack', -1, 'costo_per_ora',
                      FNumber('costo_per_ora', 'Cost per hour', 'Cost per hour', '0', 'formtable'))
        self.addField('timetrack', -1, 'costo_valuta',
                      FString('costo_valuta', 'Currency', 'Currency', 50, 255, 'euro', 'formtable'))
        # Beautifying
        self.removeFromGroup('name', '')
        self.removeFromGroup('description', '')
        self.addToGroup('name', 'timetrack', 0)
        self.addToGroup('description', 'timetrack', 1)
        myfield = self.getField('description')
        myfield.height = 10

    def getDetailIcon(self):
        return "icons/timetrack_16x16.gif"

    def getDetailTitle(self):
        return 'Timetrack'

    def getDetailColumnNames(self):
        ret = FObject.getDetailColumnNames(self)
        for x in ["fk_progetto", "fk_obj_id", "dalle_ore", "alle_ore", "ore_intervento", "ore_viaggio", "km_viaggio",
                  "luogo_di_intervento", "stato", "costo_per_ora", "costo_valuta"]:
            ret.append(x)
        return ret

    def getDecodeGroupNames(self):
        ret = FObject.getDecodeGroupNames(self)
        ret[''] = 'Details'
        ret['timetrack'] = 'Timetrack'
        return ret

    def getFilterForm(self):
        return FTimetrack()

    def getFilterFields(self):
        return ['fk_progetto', 'name', 'fk_obj_id', 'luogo_di_intervento', 'stato']

    def getListTitle(self):
        return 'Timetracks'

    def getListColumnNames(self):
        return ['fk_progetto', 'stato', 'dalle_ore', 'name', 'ore_intervento', 'fk_obj_id']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBETimetrack")()
        try:
            return DBETimetrack()
        except Exception:
            return None
formschema_type_list["FTimetrack"] = FTimetrack


class FTodo(FObject):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FObject.__init__(self, nome, azione, metodo, enctype, dbmgr)
        _mydbe = self.getDBE(dbmgr)
        # Fields
        self.addField('todo', -1, 'priority',
                      FNumber('priority', 'Priority', 'Priorita', '', 'formtable'))
        self.addField('todo', -1, 'data_segnalazione',
                      FDateTime('data_segnalazione', 'Reported on', 'Data segnalazione', dbmgr.getTodayString(),
                                'formtable', True, True))
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('fk_segnalato_da')
        self.addField('todo', -1, 'fk_segnalato_da',
                      FKField('fk_segnalato_da', 'Reporter', 'Segnalato da', '', 'formtable', _mydbe, _tmpFK, ['name'],
                              'FPeople', 'select', ' - ', 1, False))
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('fk_cliente')
        self.addField('todo', -1, 'fk_cliente',
                      FKField('fk_cliente', 'Customer', 'Cliente', '', 'formtable', _mydbe, _tmpFK, ['name'],
                              'FCompany', 'select', ' - ', 1, False))
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('fk_progetto')
        self.addField('todo', -1, 'fk_progetto',
                      FKObjectField('fk_progetto', 'Project', 'Progetto', '', 'formtable', _mydbe, _tmpFK, ['name'],
                                    '', 'select', ' - ', 1, False))
        _tmpFK = None
        if _mydbe is not None:
            _tmpFK = _mydbe.getFKDefinition('fk_tipo')
        self.addField('todo', -1, 'fk_tipo',
                      FKField('fk_tipo', 'Kind', 'Tipo', '', 'formtable', _mydbe, _tmpFK, ['name'], 'FTodoTipo',
                              'select', ' - ', 1, False))
        self.addField('todo', -1, 'stato',
                      FPercent('stato', 'State (%)', 'Stato di avanzamento %', '', 'formtable'))
        self.addField('todo', -1, 'descrizione',
                      FHtml('descrizione', 'Description', 'Descrizione del todo.', 255, '', 'formtable', -1, 5, True))
        self.addField('todo', -1, 'intervento',
                      FHtml('intervento', 'Resolution', 'Intervento eseguito.', 255, '', 'formtable', -1, 5, True))
        self.addField('todo', -1, 'data_chiusura',
                      FDateTimeReadOnly('data_chiusura', 'Closed on', 'Data chiusura', '', 'formtable', True, True))
        # Details
        self.addDetail("FTodo")
        self.addDetail("FTimetrack")
        # Beautifying
        self.removeFromGroup('name', '')
        self.addToGroup('name', 'todo', 0)

    def getDetailIcon(self):
        return "icons/task_16x16.gif"

    def getDetailTitle(self):
        return 'Task'

    def getDetailColumnNames(self):
        ret = FObject.getDetailColumnNames(self)
        ret.remove('description')
        for x in ["priority", "data_segnalazione", "fk_segnalato_da", "fk_cliente", "fk_progetto", "fk_tipo", "stato",
                  "descrizione", "intervento", "data_chiusura"]:
            ret.append(x)
        return ret

    def getDecodeGroupNames(self):
        ret = FObject.getDecodeGroupNames(self)
        ret[''] = 'Details'
        ret['todo'] = 'Task'
        return ret

    def getFilterForm(self):
        return FTodoFilter()

    def getFilterFields(self):
        return ['name', 'priority', 'data_segnalazione', 'fk_segnalato_da', 'fk_cliente', 'fk_progetto', 'father_id',
                'fk_tipo', 'stato', 'data_chiusura']

    def getListTitle(self):
        return 'Tasks'

    def getListColumnNames(self):
        return ['priority', 'data_segnalazione', 'fk_cliente', 'fk_progetto', 'father_id', 'name', 'fk_tipo', 'stato',
                'data_chiusura']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBETodo")()
        try:
            return DBETodo()
        except Exception:
            return None

    def getActions(self):
        ret = FObject.getActions(self)
        ret['start_timetracking'] = ['Start Time Tracking', 'play.png']
        ret['stop_timetracking'] = ['Stop Time Tracking', 'stop.png']
        return ret
formschema_type_list["FTodo"] = FTodo


class FTodoFilter(FTodo):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FTodo.__init__(self, nome, azione, metodo, enctype, dbmgr)
        # Fields
        self.addField('', -1, 'from_data_chiusura',
                      FDateTime('from_data_chiusura', 'Closed on >=', 'Data chiusura', '', 'formtable', True, False))
        self.addField('', -1, 'to_data_chiusura',
                      FDateTime('to_data_chiusura', 'Closed on <=', 'Data chiusura', '', 'formtable', True, False))
        self.addField('', -1, 'from_stato',
                      FPercent('from_stato', 'State (%) >=', 'Stato di avanzamento %', '', 'formtable'))
        self.addField('', -1, 'to_stato',
                      FPercent('to_stato', 'State (%) <=', 'Stato di avanzamento %', '99', 'formtable'))
        # Details
        self.addDetail("FTodo")
        self.addDetail("FTimetrack")

    def getDetailIcon(self):
        return "icons/task_16x16.gif"

    def getDetailTitle(self):
        return 'Todo Filter'

    def getDetailColumnNames(self):
        return ['name', 'priority', 'data_segnalazione', 'fk_segnalato_da', 'fk_cliente', 'fk_progetto', 'father_id',
                'fk_tipo', 'stato', 'descrizione', 'intervento', 'data_chiusura', 'owner', 'group_id', 'permissions']

    def getDetailReadOnlyColumnNames(self):
        return ['creator', 'creation_date', 'last_modify', 'last_modify_date']

    def getFilterForm(self):
        return FTodoFilter()

    def getFilterFields(self):
        return ['name', 'priority', 'data_segnalazione', 'fk_segnalato_da', 'fk_cliente', 'fk_progetto', 'father_id',
                'fk_tipo', 'from_stato', 'to_stato']

    def getListTitle(self):
        return 'Todos'

    def getListColumnNames(self):
        return ['priority', 'data_segnalazione', 'fk_cliente', 'fk_progetto', 'father_id', 'name', 'fk_tipo', 'stato',
                'data_chiusura']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBETodo")()
        try:
            return DBETodo()
        except Exception:
            return None
formschema_type_list["FTodoFilter"] = FTodoFilter


class FTodoTipo(FObject):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FObject.__init__(self, nome, azione, metodo, enctype, dbmgr)
        # Fields
        self.addField('', -1, 'order_position',
                      FString('order_position', 'Order', 'Ordine', 50, 255, '', 'formtable'))

    def getDetailTitle(self):
        return 'Todo::Kind'

    def getDetailColumnNames(self):
        return ['name', 'description', 'order_position']

    def getDetailReadOnlyColumnNames(self):
        return ['creator', 'creation_date', 'last_modify', 'last_modify_date']

    def getListTitle(self):
        return 'Todo::Kind'

    def getListColumnNames(self):
        return ['order_position', 'name', 'description']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBETodoTipo")()
        try:
            return DBETodoTipo()
        except Exception:
            return None
formschema_type_list["FTodoTipo"] = FTodoTipo

# #### Projects: end.


class FBanned(FObject):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FObject.__init__(self, nome, azione, metodo, enctype, dbmgr)
        # Fields
        self.addField('', -1, 'ban_ip',
                      FString('ban_ip', 'IP', 'Banned IP', 50, 255, '', 'formtable'))
        self.addField('', -1, 'redirected_to',
                      FString('redirected_to', 'Redirect to', 'Redirect to...', 50, 255, 'http://adf.ly/XdZw',
                              'formtable'))
        self.addField('', -1, 'give_reason',
                      FString('give_reason', 'Given reason', 'Displayed reason to the banned IP', 50, 255,
                              'Your IP has been blocked.<br/>See http://adf.ly/XdZw for more details.',
                              'formtable'))

    def getDetailIcon(self):
        return "icons/banned_16x16.png"

    def getDetailTitle(self):
        return "Banned"

    def getDetailColumnNames(self):
        return ["creation_date", "creator", "ban_ip", "description", "redirected_to", "give_reason", "owner",
                "group_id", "permissions"]

    def getDetailReadOnlyColumnNames(self):
        return ["creator", "creation_date", "last_modify", "last_modify_date"]

    def getFilterForm(self):
        return FBannedFilter()

    def getListTitle(self):
        return "Banlist"

    def getListColumnNames(self):
        return ["creation_date", "ban_ip", "description", "redirected_to", "give_reason"]

    def getPagePrefix(self):
        return "dbe"

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBEBanned")()
        try:
            return DBEBanned()
        except Exception:
            return None

    def getShortDescription(self):
        return "%s: %s" % (self.getValue('ban_ip'), self.getValue('description'))
formschema_type_list["FBanned"] = FBanned


class FBannedFilter(FBanned):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FBanned.__init__(self, nome, azione, metodo, enctype, dbmgr)
        # Fields
        self.addField('', -1, 'from_creation_date',
                      FDateTime('from_creation_date', 'Created >=', 'Data creazione', '', 'formtable', True, False))
        self.addField('', -1, 'to_creation_date',
                      FDateTime('to_creation_date', 'Created <', 'Data creazione', '', 'formtable', True, False))

    def getDetailIcon(self):
        return "icons/banned_16x16.png"

    def getDetailTitle(self):
        return "Banned Filter"

    def getDetailColumnNames(self):
        return ["creation_date", "creator", "ban_ip", "description", "redirected_to", "give_reason", "owner",
                "group_id", "permissions"]

    def getDetailReadOnlyColumnNames(self):
        return ["creator", "creation_date", "last_modify", "last_modify_date"]

    def getFilterForm(self):
        return FBannedFilter()

    def getFilterFields(self):
        return ["from_creation_date", "to_creation_date", "ban_ip", "description"]

    def getListTitle(self):
        return "Banlist"

    def getListColumnNames(self):
        return ["creation_date", "ban_ip", "description", "redirected_to", "give_reason"]

    def getPagePrefix(self):
        return "dbe"

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBEBanned")()
        try:
            return DBEBanned()
        except Exception:
            return None
formschema_type_list["FBannedFilter"] = FBannedFilter


class FMail(FFile):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FFile.__init__(self, nome, azione, metodo, enctype, dbmgr)
        #_mydbe = self.getDBE(dbmgr)
        # Fields
        self.addField('email', -1, 'msgid',
                      FString('msgid', 'Message ID', 'Message ID', 50, 255, '', 'formtable'))
        self.addField('email', -1, 'msgdate',
                      FDateTime('msgdate', 'Date', 'Date', dbmgr.getTodayString(), 'formtable', True, True))
        self.addField('email', -1, 'msgfrom',
                      FString('msgfrom', 'From', 'From', 50, 255, '', 'formtable'))
        self.addField('email', -1, 'msgto',
                      FString('msgto', 'To', 'To', 50, 255, '', 'formtable'))
        self.addField('email', -1, 'msgcc',
                      FString('msgcc', 'CC', 'CC', 50, 255, '', 'formtable'))
        self.addField('email', -1, 'subject',
                      FString('subject', 'Subject', 'Subject', 50, 255, '', 'formtable'))
        self.addField('email', -1, 'msgbody',
                      FTextArea('msgbody', 'Body', 'Message body', 255, '', 'formtable', -1, -1, True))
        self.removeFromGroup('filename', 'file')
        #self.removeFromGroup('description', '')
        self.addToGroup('filename', 'email', 0)
        #self.addToGroup('description', 'file', 1)

    def getDetailIcon(self):
        return "icons/email_16x16.png"

    def getDetailTitle(self):
        return 'Email'

    def getDetailColumnNames(self):
        ret = FFile.getDetailColumnNames(self)
        for x in ["msgid", "msgdate", "msgfrom", "msgto", "msgcc", "subject", "msgbody"]:
            ret.append(x)
        return ret

    def getDetailReadOnlyColumnNames(self):
        ret = FFile.getDetailReadOnlyColumnNames(self)
        for x in ["msgid", "msgdate", "msgfrom", "msgto", "msgcc", "subject", "msgbody"]:
            ret.append(x)
        return ret

    def getFilterForm(self):
        return FMailFilter()

    def getFilterFields(self):
        return ['father_id', 'name', 'description']

    def getListTitle(self):
        return 'Emails'

    def getGroupNames(self):
        return ['email', 'file', '', '_permission']

    def getDecodeGroupNames(self):
        ret = FFile.getDecodeGroupNames(self)
        ret['email'] = 'Email'
        return ret

    def getListColumnNames(self):
        return ['msgdate', 'msgfrom', 'subject', 'msgto', 'msgcc']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBEMail")()
        try:
            return DBEMail()
        except Exception:
            return None
formschema_type_list["FMail"] = FMail


class FMailFilter(FMail):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FMail.__init__(self, nome, azione, metodo, enctype, dbmgr)
        # Fields
        self.addField('', -1, 'from_msgdate',
                      FDateTime('from_msgdate', 'Date >=', 'Date', '', 'formtable', True, False))
        self.addField('', -1, 'to_msgdate',
                      FDateTime('to_msgdate', 'Date <', 'Date', '', 'formtable', True, False))

    def getDetailIcon(self):
        return "icons/email_16x16.png"

    def getDetailTitle(self):
        return 'Email Filter'

    def getDetailColumnNames(self):
        return ['father_id', 'path', 'filename', 'checksum', 'mime', 'alt_link', 'owner', 'group_id', 'permissions',
                'msgdate', 'msgfrom', 'msgto', 'msgcc', 'subject', 'msgbody', 'msgid']

    def getDetailReadOnlyColumnNames(self):
        return ['creator', 'creation_date', 'last_modify', 'last_modify_date', 'checksum', 'mime', 'msgdate', 'msgfrom',
                'msgto', 'msgcc', 'subject', 'msgbody']

    def getFilterForm(self):
        return FMailFilter()

    def getFilterFields(self):
        return ['from_msgdate', 'to_msgdate', 'msgfrom', 'msgto', 'msgcc', 'subject']

    def getListTitle(self):
        return 'Emails'

    def getListColumnNames(self):
        return ['msgdate', 'msgfrom', 'msgto', 'msgcc', 'subject']

    def getPagePrefix(self):
        return 'dbe'

    def getDBE(self, dbmgr=None):
        if dbmgr is not None:
            return dbmgr.getClazzByTypeName("DBEMail")()
        try:
            return DBEMail()
        except Exception:
            return None
# FIXME formschema_type_list["FMailFilter"]=FMailFilter
