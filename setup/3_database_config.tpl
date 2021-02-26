<h2>Step 3: Database configuration</h2>
<table class="stepTable">
 <tr>
  <td colspan="3" class="stepHeadline">
   Connection settings
  </td>
 </tr><tr>
  <td>&nbsp;</td>
  <td class="stepConfBorder" colspan="2" align="justify">
   The root user must
   exist and should have full access rights to the database system in order to create the i-doit user. Hence
   the i-doit account is used to access the database system from the i-doit framework.
   Enter the connection settings for your MySQL Database here:
  </td>
 </tr><tr>
  <td>&nbsp;</td>
  <td class="stepConfTitle">
   Host (max. 60 char):
  </td>
  <td class="stepConfContent">
  	<input class="confInputDir" type="text" id="config.db.host.field" name="config.db.host.field" value="[CONFIG.DB.HOST]" />
  </td>
  <td>&nbsp;</td>
 </tr><tr>
  <td>&nbsp;</td>
  <td class="stepConfTitle">
   Port:
  </td>
  <td class="stepConfContent">
  	<input maxlength="5" class="confInputDir" type="text" id="config.db.port.field" name="config.db.port.field" value="[CONFIG.DB.PORT]" />
  </td>
  <td>&nbsp;</td>
  </tr>
  <tr>
  <td>&nbsp;</td>
  <td class="stepConfTitle">
   Username (root):
  </td>
  <td class="stepConfContent">
  	<input class="confInputDir" type="text" id="config.db.root.username.field" name="config.db.root.username.field" value="[CONFIG.DB.ROOT.USERNAME]" />
  </td>
  <td></td>
 </tr><tr>
  <td>&nbsp;</td>
  <td class="stepConfTitle">
   Password:
  </td>
  <td class="stepConfContent">
  	<input class="confInputDir" type="password" id="config.db.root.password.field" name="config.db.root.password.field" value="[CONFIG.DB.ROOT.PASSWORD]" />
  </td>
  <td></td>
 </tr><!--<tr>
  <td>&nbsp;</td>
  <td class="stepConfTitle">Retype password:</td>
  <td class="stepConfContent">
  	<input class="confInputDir" type="password" id="config.db.root.password2.field" name="config.db.root.password2.field" value="[CONFIG.DB.ROOT.PASSWORD2]" />
  </td>-->
  <td></td>
 </tr>
 <tr>
  <td colspan="3" class="stepHeadline">
   MySQL user settings
  </td>
 </tr><tr>
  <td>&nbsp;</td>
  <td class="stepConfBorder" colspan="2" align="justify">
   Please enter username and password for a new MySQL user (This user will be authorized to the i-doit databases only).
  </td>
 </tr><tr>
  <td>&nbsp;</td>
  <td class="stepConfTitle">
   Username (max. 16 & no special chars):
  </td>
  <td class="stepConfContent">
  	<input class="confInputDir" onchange="CheckDatabaseName('config.db.username.field', 'Your username has got special charactes. Only a-z & A-Z is allowed here. Please correct your value.'); return false;" type="text" id="config.db.username.field" name="config.db.username.field" value="[CONFIG.DB.USERNAME]" />
  </td>
  <td></td>
 </tr><tr>
  <td>&nbsp;</td>
  <td class="stepConfTitle">
   Password (i-doit):
  </td>
  <td class="stepConfContent">
  	<input class="confInputDir" type="password" id="config.db.password.field" name="config.db.password.field" value="[CONFIG.DB.PASSWORD]" />
  </td>
  <td></td>
 </tr><tr>
  <td>&nbsp;</td>
  <td class="stepConfTitle">Retype password:</td>
  <td class="stepConfContent">
  	<input class="confInputDir" type="password" id="config.db.password2.field" name="config.db.password2.field" value="[CONFIG.DB.PASSWORD2]" />
  </td>
  <td></td>
 </tr>
 <tr>
  <td colspan="3" class="stepHeadline">
   Database settings
  </td>
 </tr><tr>
  <td>&nbsp;</td>
  <td class="stepConfBorder" colspan="2" align="justify">
   <b>i-doit</b> supports multi-client capability. Each client needs an own database - this name can be entered in <i>Mandant Database Name</i>.
   For all global and framework-internal data the system database is used - this name can be entered in <i>System Database Name</i>.
   <br /><br />
   <b>Be aware that both database names only allow the chars 0-9, a-z, A-Z and _</b>
  </td>
 </tr><tr>
  <td>&nbsp;</td>
  <td class="stepConfTitle">
   System Database Name (max. 64 char):
  </td>
  <td class="stepConfContent">
  	<input onChange="CheckDatabaseName('SystemName'); return false;" id="SystemName" class="confInputDir" type="text" name="config.db.name.field" value="[CONFIG.DB.NAME]" />
  </td>
  <td></td>
 </tr><tr>
  <td>&nbsp;</td>
  <td class="stepConfTitle">
   Mandator Database Name (max. 64 char):
  </td>
  <td class="stepConfContent">
  	<input onChange="CheckDatabaseName('MandatorName'); return false;" id="MandatorName" class="confInputDir" type="text" name="config.mandant.name.field" value="[CONFIG.MANDANT.NAME]" />
  </td>
  <td></td>
 </tr><tr>
  <td>&nbsp;</td>
  <td class="stepConfTitle">
   Mandator title:
  </td>
  <td class="stepConfContent">
  	<input class="confInputDir" type="text" id="config.mandant.title.field" name="config.mandant.title.field" value="[CONFIG.MANDANT.TITLE]" />
  </td>
  </tr>
  <tr>
      <td>&nbsp;</td>
      <td class="stepConfTitle">
   Start value for object/configuration item IDs:
  </td>
  <td class="stepConfContent">
  	<input class="confInputDir" type="text" id="config.mandant.autoinc.field" name="config.mandant.autoinc.field" value="[CONFIG.MANDANT.AUTOINC]" />
  </td>
 </tr>
</table>