# -*- coding: utf-8 -*-

#
# @copyright &copy; 2012 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
# @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
# @version $Id: __init__.py $
# @package formulator
#
# Permission to use, copy, modify, and distribute this software for any
# purpose with or without fee is hereby granted, provided that the above
# copyright notice and this permission notice appear in all copies.
#
# THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
# WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
# MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
# ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
# WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
# ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
# OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
#

class FField:
    def __init__(self, aNomeCampo, aTitle, aDescription, aSize, aLength=20, aValore='', aClasseCss=None, myform=None, mytipo='s'):
        self.aNomeCampo = aNomeCampo
        self._title = aTitle
        self._description = aDescription
        self._size = aSize
        self._length = aLength
        self.aValore = aValore
        self.aClasseCss = aClasseCss
        self.myform = myform
        self.tipo = mytipo
    def render(self):
        raise Exception("TODO")
    def render_view(self):
        raise Exception("TODO")
    def render_hidden(self):
        raise Exception("TODO")
    def render_readonly(self):
        raise Exception("TODO")
    def readValueFromRequest(self, request, prefix='field_'):
        self.readValueFromArray(request, prefix)
    def readValueFromArray(self, aArray, prefix='field_'):
        if aArray.has_key("%s%s" % (prefix, self.aNomeCampo)):
            self.aValore = aArray["%s%s" % (prefix, self.aNomeCampo)]
        else:
            self.aValore = None
    def writeValueToArray(self, aArray, prefix='field_'):
        aArray["%s%s" % (prefix, self.aNomeCampo)] = self.aValore
        return aArray
    def clean(self):
        """Resets the field value."""
        self.setValue('')
    def getValue(self):
        return self.aValore
    def setValue(self, v):
        if v == '':
            self.aValore = None
        else:
            self.aValore = v
    def GetTitle(self):
        return self._title
    def GetDescription(self):
        return self._description

class FForm:
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None, formFactory=None):
        self.fields = {}
        self.groups = {}
        self.nome = nome
        self.azione = azione
        self.metodo = metodo
        self.enctype = enctype
        self.dbmgr = dbmgr
        self.formFactory = formFactory
    def toString(self):
        ret = "Nome: %s\n" % self.nome
        ret += "Azione: %s\n" % self.azione
        ret += "Metodo: %s\n" % self.metodo
        gruppi = self.getGroupNames()
        ret += "Gruppi: %s\n" % gruppi
        for nomeGruppo in gruppi:
            ret += "\tGruppo: %s\n" % nomeGruppo
            mygruppo = self.getGroup(nomeGruppo)
            for i in range(len(mygruppo)):
            #for i in range(mygruppo):
                myfield = self.getField(mygruppo[i])
                ret += "\t\t%s => '%s'\n" % (mygruppo[i], myfield.getValue())
        return ret
    def getAction(self):
        """Azione di default: serve per la form html."""
        return self.azione
    def getMethod(self):
        return self.metodo
    def getName(self):
        return self.nome
    def getEnctype(self):
        return self.enctype
    # OVERRIDE: start.
    def getDetailIcon(self):
        return ""
    def getDetailTitle(self):
        return ""
    def getDetailColumnNames(self):
        return []
    def getDetailReadOnlyColumnNames(self):
        """Ritorna i nomi dei campi in read-only."""
        return []
    def getFilterForm(self):
        return None
    def getFilterFields(self):
        return []
    def getListTitle(self):
        return ""
    def getListColumnNames(self):
        """Ritorna i nomi di fields da visualizzare in una lista."""
        return []
    def getListEditableColumnNames(self):
        """Ritorna i nomi di fields editabili in lista."""
        return []
    def getDecodeGroupNames(self):
        """Ritorna un array con nome_gruppo=>'Nome Gruppo'."""
        return {}
    def getPagePrefix(self):
        return ""
    def getDBE(self):
        """Ritorna la dbe associata alla form."""
        return None
    def getCodice(self):
        """Ritorna il codice che identifica la descrizione."""
        return ""
    def getShortDescription(self, dbmgr=None):
        """Ritorna una breve descrizione dei valori contenuti."""
        return ""
    def getController(self):
        """Ritorna il nome del controller della form."""
        return ""
    def getActions(self):
        """Ritorna le azioni possibili sul tipo di dato gestito.
        L'array tornato e' del tipo: codice_azione => array('Label azione','immagineAzione.jpg')
        Le azioni saranno date in pasto assieme ai parametri modificati ad un controller."""
        return {}
    # OVERRIDE: end.
    # Fields: start.
    def addField(self, nomeGruppo, ordine, nomeField, aField):
        aField.myform = self
        self.fields[nomeField] = aField
        self.addToGroup(nomeField, nomeGruppo, ordine)
        if isinstance(aField, FFileField):
            self.enctype = 'multipart/form-data'
    def getFieldNames(self):
        return self.fields.keys()
    def getField(self, fieldName):
        if not self.fields.has_key(fieldName):
            return None
        if self.fields[fieldName].myform is None:
            self.fields[fieldName].myform = self
        return self.fields[fieldName]
    # Fields: end.
    # Groups: start.
    def decodeGroupName(self, group_name):
        tmp = self.getDecodeGroupNames()
        if tmp.has_key(group_name):
            return tmp[group_name]
        else:
            return group_name
    def getGroupNames(self):
        return self.groups.keys()
    def getGroup(self, nomeGruppo):
        if self.groups.has_key(nomeGruppo):
            return self.groups[nomeGruppo]
        else:
            return []
    def getGroupSize(self, nomeGruppo):
        return len(self.getGroup(nomeGruppo))
    def setGroup(self, nomeGruppo, gruppo):
        self.groups[nomeGruppo] = gruppo
    def addToGroup(self, nomeField, nomeGruppo='', ordine=-1):
        """Un gruppo e' un array ordinato di nomi di campo."""
        gruppo = self.getGroup(nomeGruppo)
        if ordine < 0: ordine = len(gruppo)
        if ordine >= len(gruppo):
            gruppo.append(nomeField)
        else:
            gruppo.insert(ordine, nomeField)
            #gruppo[ordine] = nomeField
        self.setGroup(nomeGruppo, gruppo)
    def removeFromGroup(self, nomeField, nomeGruppo):
        gruppo = self.getGroup(nomeGruppo)
        i = gruppo.index(nomeField)
        if i >= 0:
            gruppo.pop(i)
        self.setGroup(nomeGruppo, gruppo)
    # Groups: end.
    # Request: start.
    def readValuesFromRequest(self, aRequest, prefix="field_"):
        for nomeCampo in self.getFieldNames():
            self.fields[nomeCampo].readValueFromRequest(aRequest, prefix)
    def readValuesFromArray(self, aSession, prefix="field_"):
        self.readValuesFromRequest(aSession, prefix)
    def writeValuesToArray(self, aSession, prefix="field_"):
        for nomeCampo in self.getFieldNames():
            self.fields[nomeCampo].writeValueToArray(aSession, prefix)
    # Request: end.
    # Getters and setters: start.
    def clean(self):
        """Resets all the field values."""
        for n in self.getFieldNames():
            self.fields[n].clean()
    def getValues(self):
        ret = {}
        for nomeCampo in self.getFieldNames():
            if self.fields.has_key(nomeCampo):  # 2012.04.17
                ret[nomeCampo] = self.fields[nomeCampo].getValue()
        return ret
    def setValues(self, valori):
        for chiave in valori.keys():
            campo = self.getField(chiave)
            if campo is not None:
                self.fields[chiave].setValue(valori[chiave])
    def getValue(self, fieldName):
        tmp = self.getField(fieldName)
        if tmp is None:
            return None
        return tmp.getValue()
    def setValue(self, fieldName, aValue):
        self.getField(fieldName).setValue(aValue)
    def setFormFactory(self, formFactory):
        self.formFactory = formFactory
    def getFormFactory(self):
        return self.formFactory
    # Getters and setters: end.
    # Render: start.
    def render(self, dbmgr=None):
        raise Exception("TODO")
    def render_view(self, dbmgr=None):
        raise Exception("TODO")
    def render_filter(self, dbmgr=None):
        raise Exception("TODO")
    # Render: end.

class FMasterDetail(FForm):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        FForm.__init__(self, nome, azione, metodo, enctype, dbmgr)
        self.detailForms = []
        # Contenitore delle dbe figlie della corrente: 'nomeform'=>lista dbe figli
        self.childs = {}
    def addDetail(self, aDetail, cardinality="n"):
        """Aggiunge una form di dettaglio.
        @param aDetail istanza di form OPPURE stringa col nome della classe FForm da istanziare
        @param cardinality 1=solo un figlio n=n-figli"""
        self.detailForms.append(aDetail)
    def getDetailFormsCount(self):
        return len(self.detailForms)
    def getDetailName(self, i):
        return self.detailForms[i]
    def getDetail(self, i):
        if type(self.detailForms[i]) == str:
            ret = (self.getFormFactory().getInstance(self.detailForms[i], dbmgr=self.dbmgr))
            return ret
        else:
            return self.detailForms[i]
    def setValues(self, valori, doFetchChilds=False):
        FForm.setValues(self, valori)
        if doFetchChilds:
            self.fetchChilds()
    def fetchChilds(self):
        print "FMasterDetail.fetchChilds: self.dbmgr=%s" % self.dbmgr
        mydbe = self.getDBE(self.dbmgr)
        print "FMasterDetail.fetchChilds: %s - mydbe=%s" % (self.__class__.__name__, mydbe)
        if mydbe is None:
            return
        mydbe.setValuesDictionary(self.getValues())
        print "FMasterDetail.fetchChilds: mydbe=%s" % (mydbe)
        for detail in self.detailForms:
            print "FMasterDetail.fetchChilds: Detail: %s" % (detail)
            childForm = self.getFormFactory().getInstance(detail, dbmgr=self.dbmgr)
            print "FMasterDetail.fetchChilds: childForm: %s - %s - %s" % (childForm.getName(), childForm.getDetailTitle(), childForm.getListTitle())
            childDbe = childForm.getDBE(self.dbmgr)
            childDbe.readFKFrom(mydbe)
            print "FMasterDetail.fetchChilds: childDbe=%s" % (childDbe)
            tmp = self.dbmgr.search(childDbe, uselike=0)
            if isinstance(childForm, FAssociation):
                print "FMasterDetail.fetchChilds: TODO FAssociation"
                tmp2 = []
                dest_form = None
                if detail == childForm.getFromForm():
                    dest_form = childForm.getToForm()
                else:
                    dest_form = childForm.getFromForm()
                dest_form = self.getFormFactory().getInstance(dest_form, dbmgr=self.dbmgr)
                dest_dbe = dest_form.getDBE(self.dbmgr)
                for assdbe in tmp:
                    dest_dbe = assdbe.writeFKTo(dest_dbe)
                    tmplist = self.dbmgr.search(dest_dbe, uselike=0)
                    tmp2.append(tmplist[0])
                tmp = tmp2
            print "FMasterDetail.fetchChilds: tmp=%s" % (tmp)
            self.childs[detail] = tmp

class FAssociation(FForm):
    """Classe base per le form responsabili della mappatura di una DBE che rappresenta una
    associazione N-M su DB. Questa associazione puo' presentare N-attributi, renderizzabili tramite
    fields."""
    def __init__(self, dbeassociation, from_form, to_form, nome='', azione='', metodo="POST"):
        FForm.__init__(self, nome, azione, metodo)
        self.dbeassociation = dbeassociation
        self.from_form = from_form
        self.to_form = to_form
    def getDBE(self):
        return self.dbeassociation
    def getFromForm(self):
        return self.from_form
    def getToForm(self):
        return self.to_form

class FormFactory:
    """Returns the correct class for the given dbe name."""
    def __init__(self, verbose=False):
        self.verbose = verbose
        self.classname2type = {"default": "FForm", }
        self.dbename2type = {"default": "FForm", }
        self.dbename2classname = {"default": "FForm", }
    def register(self, aClassName, clazz=None, dbmgr=None, package='rra.formulator'):
        istanza = None
        if clazz is not None:
            istanza = clazz('', '', 'POST', '', dbmgr)
        else:
            exec(compile("from %s import %s\nistanza = %s()" % (package, aClassName, aClassName), "formulatorcompile.py", "exec"))
        self.classname2type[aClassName] = clazz  # aClassName
        mydbe = istanza.getDBE(dbmgr)
        if mydbe is None:
            return
        if self.dbename2type.has_key(mydbe.getTypeName()):
            return
        self.dbename2type[mydbe.getTypeName()] = clazz  # aClassName
        self.dbename2classname[mydbe.getTypeName()] = aClassName
    def getAllClassnames(self):
        return self.classname2type.keys()
    def getInstance(self, aClassname, nome='', azione='', metodo="POST", dbmgr=None):
        if self.classname2type.has_key(aClassname):
            ret = (self.classname2type[aClassname])(nome, azione, metodo, '', dbmgr)
            ret.setFormFactory(self)
            return ret
        else:
            return FForm(nome, azione, metodo, '', dbmgr)
    def getInstanceByDBEName(self, aDBEName, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        if self.dbename2type.has_key(aDBEName):
            ret = (self.dbename2type[aDBEName])(nome, azione, metodo, enctype, dbmgr)
            ret.setFormFactory(self)
            return ret
        else:
            return FForm(nome, azione, metodo)
    def getRemoteSchema(self, url, filename=None, toolkit='wxgui'):
        import xmlrpclib
        s = xmlrpclib.Server(url)
        ret = s.getFormSchema()
        src = "# -*- coding: utf-8 -*-\n"
        if toolkit > '':
            src += "from rprj.formulator.%s import *\n" % (toolkit)
        else:
            src += "from rprj.formulator import *\n"
        src += ret[0].data
        if filename is not None:
            nomemodulo = filename
            if not filename.endswith(".py"):
                nomemodulo = filename
                filename += ".py"
            else:
                nomemodulo = filename[:-3]
            file(filename, 'w').write(src)
            #__import__(nomemodulo)
            return None
        #print "# ##################################"
        #print src
        #print "# ##################################"
        exec(compile(src, "remoteformschema.py", "exec"))
        #print "# ##################################"
        #print dir()
        #print dir(FUser)
        #print FUser.__module__
        #print "# ##################################"
        return formschema_type_list
    def loadRemoteSchema(self, url, dbmgr=None, filename=None, toolkit='wxgui'):
        formschema_type_list = self.getRemoteSchema(url, filename, toolkit)
        if filename is not None:
            nomemodulo = filename
            if not filename.endswith(".py"):
                nomemodulo = filename
            else:
                nomemodulo = filename[:-3]
            mymodule = __import__(nomemodulo)
            formschema_type_list = mymodule.formschema_type_list
        for k in formschema_type_list.keys():
            self.register(k, formschema_type_list[k], dbmgr)

# ######################### Campi Standard

class FFileField(FField):
    def __init__(self, aNomeCampo, aTitle, aDescription, dest_directory='', aSize=1, aLength=20,
                 aValore='', aClasseCss=None):
        """@param dest_directory directory dove memorizzare i files."""
        FField.__init__(self, aNomeCampo, aTitle, aDescription, aSize, aLength, aValore, aClasseCss)
        self.dest_directory = dest_directory
    def generaFilename(self):
        dbe = self.myform.getDBE()
        dbe.setValuesDictionary(self.myform.getValues())
        return dbe.generaFilename()
    def render_view(self):
        raise Exception("TODO")
    def render(self):
        raise Exception("TODO")
    def readValueFromArray(self, aArray, prefix="field_"):
        raise Exception("TODO")
class FNumber(FField):
    def __init__(self, aNomeCampo, aTitle, aDescription, aValore='', aClasseCss=None, myform=None):
        FField.__init__(self, aNomeCampo, aTitle, aDescription, 0, 0, aValore, aClasseCss, myform, 'n')
class FPercent(FNumber):
    def render(self):
        raise Exception("TODO")
    def render_view(self):
        raise Exception("TODO")
class FString(FField):
    pass
class FPassword(FString):
    def render(self):
        raise Exception("TODO")
    def render_view(self):
        raise Exception("TODO")
class FServerFile(FFileField):
    def __init__(self, aNomeCampo, aTitle, aDescription, aSize, aLength=20, aValore='', aClasseCss=None, aImagePath=''):
        FFileField.__init__(self, aNomeCampo, aTitle, aDescription, aSize, aLength, aValore, aClasseCss)
        self.imagePath = aImagePath
    def render(self):
        raise Exception("TODO")
class FServerImage(FFileField):
    def __init__(self, aNomeCampo, aTitle, aDescription, aSize, aLength=20, aValore='',
        aClasseCss = None, aImagePath=''):
        FFileField.__init__(self, aNomeCampo, aTitle, aDescription, aSize, aLength, aValore,
                            aClasseCss)
        self.imagePath = aImagePath
    def render(self):
        raise Exception("TODO")
class FList(FField):
    def __init__(self, aNomeCampo, aTitle, aDescription, aSize, aLength=20, aValore='',
                 aClasseCss=None, listaValori={}, altezza=1, multiselezione=False):
        """listaValori = {  k=>v  }
        altezza = numero di elementi visualizzati della select
        multiselezione"""
        FField.__init__(self, aNomeCampo, aTitle, aDescription, aSize, aLength, aValore, aClasseCss)
        self.listaValori = listaValori
        self.altezza = altezza
        self.multiselezione = multiselezione
    def render(self):
        raise Exception("TODO")
    def render_view(self):
        raise Exception("TODO")
class FCheckBox(FField):
    def __init__(self, aNomeCampo, aTitle, aDescription, aSize, aLength=20, aValore='',
                 aClasseCss=None, listaValori={}, multiselezione=False, stringa_separatrice="||",
                 tipo_campo='s'):
        """listaValori = {  k=>v  }
        altezza = numero di elementi visualizzati della select
        multiselezione
        stringa_separatrice: nel caso della multiselezione, viene effettuata una implosione
        dell'array di stringhe con qyesta stringa come separatore"""
        FField.__init__(self, aNomeCampo, aTitle, aDescription, aSize, aLength, aValore, aClasseCss)
        self.listaValori = listaValori
        self.multiselezione = multiselezione
        self.stringa_separatrice = stringa_separatrice
        self.tipo = tipo_campo
    def setValue(self, v):
        if self.multiselezione and type(v) == list:
            self.aValore = self.stringa_separatrice.join(v)
        else:
            self.aValore = v
    def readValueFromRequest(self, aRequest):
        if aRequest.has_key('field_%s' % self.aNomeCampo):
            self.setValue(aRequest['field_%s' % self.aNomeCampo])
    def render(self):
        raise Exception("TODO")
    def render_view(self):
        raise Exception("TODO")
class FTextArea(FField):
    def __init__(self, aNomeCampo, aTitle, aDescription, aSize, aValore='', aClasseCss=None,
                 width=None, height=None, basicFormatting=True):
        """basic_formatting:supporto per una formattazione di base, al momento solo gli 'a capo'."""
        FField.__init__(self, aNomeCampo, aTitle, aDescription, aSize, 0, aValore, aClasseCss)
        self.width = width
        self.height = height
        self.basicFormatting = basicFormatting
    def render(self):
        raise Exception("TODO")
    def render_view(self):
        raise Exception("TODO")
class FDateTime(FField):
    def __init__(self, aNomeCampo, aTitle, aDescription, aValore, aClasseCss=None,
                 aVisualizzaData=True, aVisualizzaOra=True):
        FField.__init__(self, aNomeCampo, aTitle, aDescription, 0, 0, aValore, aClasseCss,
                        myform=None, mytipo='d')
        self.aVisualizzaData = aVisualizzaData
        self.aVisualizzaOra = aVisualizzaOra
    def clean(self):
        """Resets the field value."""
        self.setValue(None)
    def readValueFromRequest(self, aRequest):
        tmpdata = ''
        if aRequest.has_key("subfield_%s_date" % self.aNomeCampo):
            tmpdata = aRequest["subfield_%s_date" % self.aNomeCampo]
        else:
            anno = ''
            if aRequest.has_key("subfield_%s_year" % self.aNomeCampo):
                anno = aRequest["subfield_%s_year" % self.aNomeCampo]
            mese = ''
            if aRequest.has_key("subfield_%s_month" % self.aNomeCampo):
                mese = aRequest["subfield_%s_month" % self.aNomeCampo]
            if len(mese) == 1:
                mese = "0%s" % mese
            giorno = ''
            if aRequest.has_key("subfield_%s_day" % self.aNomeCampo):
                giorno = aRequest["subfield_%s_day" % self.aNomeCampo]
            if len(giorno) == 1:
                giorno = "0%s" % giorno
            if len(anno) > 0 and len(mese) > 0 and len(giorno) > 0:
                tmpdata = "%s/%s/%s" % (anno, mese, giorno)
        tmpora = ''
        ore = ''
        if aRequest.has_key("subfield_%s_hour" % self.aNomeCampo):
            ore = aRequest["subfield_%s_hour" % self.aNomeCampo]
        minuti = ''
        if aRequest.has_key("subfield_%s_minute" % self.aNomeCampo):
            minuti = aRequest["subfield_%s_minute" % self.aNomeCampo]
        if len(ore) > 0 and len(minuti) > 0:
            tmpora = "%s:%s" % (ore, minuti)
        if tmpdata != '':
            self.aValore = tmpdata
            if tmpora != '':
                self.aValore = "%s %s" % (self.aValore, tmpora)
        else:
            if self.aVisualizzaOra:
                self.aValore = tmpora
            else:
                self.aValore = ''
    def render(self):
        raise Exception("TODO")
    def render_view(self):
        raise Exception("TODO")
    def render_hidden(self):
        raise Exception("TODO")
class FDateTimeReadOnly(FDateTime):
    def render(self):
        return self.render_readonly()
class FKField(FField):
    def __init__(self, aNomeCampo, aTitle, aDescription, aValore=None, aClasseCss=None, mydbe=None,
                 myFK=None, description_columns=[], destform=None, viewmode='select',
                 aDescription_glue=" - ", aAltezza=1, aMultiselezione=False):
        """@param viewmode { 'select','readonly','distinct' }.
        @param destform nome della classe formulator del tipo di destinazione
        @param description_columns array con le colonne descrizione della tabella puntata"""
        FField.__init__(self, aNomeCampo, aTitle, aDescription, 0, 0, aValore, aClasseCss)
        self.mydbe = mydbe
        self.myFK = myFK
        if myFK is not None and len(myFK) > 0:
            self.myFK = myFK[0]
        self.viewmode = viewmode
        self.destform = destform
        self.description_columns = description_columns
        self.description_glue = aDescription_glue
        self.altezza = aAltezza
        self.multiselezione = aMultiselezione

        self.listaValori = None

    def GetListaValori(self, refresh=False):
        if self.listaValori is None or refresh:
            self.listaValori = {}
            self.listaValori[""] = ""
            for myfk in self.myFK:
                cerca = self.myform.dbmgr.getClazz(myfk.tabella_riferita)
                lista = self.myform.dbmgr.search(cerca, cerca.getOrderBy())
                for dbe in lista:
                    chiavi = dbe.getKeys().keys()
                    chiave_array = ["%s" % dbe.getValue(chiave) for chiave in chiavi]
                    description_array = [dbe.getValue(chiave) for chiave in self.description_columns]
                    self.listaValori["_".join(chiave_array)] = self.description_glue.join(description_array)
        return self.listaValori
    def renderlink(self):
        link = ""
        link_desc = ""
        for myfk in self.myFK:
            cerca = self.myform.dbmgr.getClazz(myfk.tabella_riferita)
            cerca.setValue(myfk.colonna_riferita, self.aValore)
            lista = self.myform.dbmgr.search(cerca, cerca.getOrderBy())
            if len(lista) == 1:
                mydbe = lista[0]
                description_array = []
                for chiave in self.description_columns:
                    description_array.append(mydbe.getValue(chiave))
                link_desc = self.description_glue.join(description_array)
                break
        return link, link_desc

    def render_distinct(self, dbmgr):
        raise Exception("TODO")
    def render(self, dbmgr):
        if self.viewmode == 'select':
            raise Exception("TODO")
        elif self.viewmode == 'distinct':
            return self.render_distinct(dbmgr)
        elif self.viewmode == 'readonly':
            raise Exception("TODO")
        else:
            raise Exception("TODO")
    def render_view(self, dbmgr):
        raise Exception("TODO")
    def render_readonly(self, dbmgr):
        raise Exception("TODO")
class FKObjectField(FKField):
    """A Foreign Key pointing to multiple destinations.
    @param destform se null ==> cerca tra tutte le FObject, nella form specificata altrimenti"""
    def __init__(self, aNomeCampo, aTitle, aDescription, aValore=None, aClasseCss=None, mydbe=None,
                 myFK=None, description_columns=[], destform=None, viewmode='select',
                 aDescription_glue=" - ", aAltezza=1, aMultiselezione=False):
        FKField.__init__(self, aNomeCampo, aTitle, aDescription, aValore, aClasseCss, mydbe, myFK,
                         description_columns, destform, viewmode, aDescription_glue, aAltezza,
                         aMultiselezione)
    def _searchObject(self, dbmgr, formFactory):
        mydbe = None
        mydestform = None
        mynomeclasse = ''
        # dbeFactory = dbmgr.getFactory()
        classi = formFactory.getAllClassnames()
        if self.myFK is not None:
            for nomeclasse in classi:
                if nomeclasse == 'default': continue
                exec(compile("mydestform = %s()" % (nomeclasse), "formulatorcompile.py", "exec"))
                if not isinstance(mydestform, FObject): continue
                cerca = mydestform.getDBE()
                cerca.setValue(self.myFK.colonna_riferita, self.aValore)
                lista = dbmgr.search(cerca, cerca.getOrderBy())
                if len(lista) == 1 and \
                        lista[0].getValue(self.myFK.colonna_riferita) == cerca.getValue(self.myFK.colonna_riferita):
                    mydbe = lista[0]
                    # mynomeclasse = nomeclasse
                    break
        return [mydbe, mydestform, nomeclasse]
    def render_distinct(self, dbmgr):
        raise Exception("TODO")
    def render(self, dbmgr):
        raise Exception("TODO")
    def render_view(self, dbmgr, showlink=False):
        raise Exception("TODO")

class FHtml(FTextArea):
    pass
class FUuid(FString):
    pass
class FPermissions(FString):
    pass
class FLanguage(FString):
    pass
class FChildSort(FList):
    pass

# Plugin RRA
class FIPAddress(FString):
    pass
