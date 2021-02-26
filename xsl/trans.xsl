<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template match="isys_export">
	<html>
		<head>
			<title>i-doit</title>
			<link rel="stylesheet" type="text/css" href="%%BASE_URL%%xsl/trans.css" />
		</head>
		<body>
			<div id="banner">
				<img alt="Logo" src="%%BASE_URL%%images/logo.png"/>
				<xsl:apply-templates select="head"/>
			</div>

			<xsl:apply-templates select="objects"/>
			<xsl:apply-templates select="contacts"/>
		</body>
	</html>
</xsl:template>


<xsl:template match="head">
	<div id="head">
		<h2><xsl:value-of select="mandator"/></h2>
	</div>
	<div style="float:right;">
		<xsl:value-of select="datetime"/>
	</div>
</xsl:template>


<xsl:template match="objects">
	<div id="objects">
		<h2>{*LC__CMDB__CATG__OBJECT*}</h2>
		<xsl:apply-templates select="object"/>
	</div>
</xsl:template>

<xsl:template match="contacts">
	<div id="contacts">
		<h3>{*LC__NAVIGATION__MAINMENU__TITLE_CONTACT*}</h3>
		<xsl:apply-templates select="contact"/>
	</div>
</xsl:template>


<xsl:template match="object">
	<div class="object">
		<div class="obj_title">
			<a name="obj_{id}">
			<h2 class="m0"><xsl:value-of select="title"/> (<xsl:value-of select="type"/>)</h2>
			</a>
		</div>
		<xsl:apply-templates select="data"/>
	</div>
</xsl:template>


<xsl:template match="contact">

</xsl:template>


<xsl:template match="data">
	<xsl:apply-templates select="category"/>
</xsl:template>


<xsl:template match="category">
	<div class="category">
		<div class="cat_title">
			<h3 class="m0"><xsl:value-of select="@title"/></h3>
		</div>

		<div class="cat_data">
			<xsl:choose>
				<xsl:when test="@const = 'C__CATG__GLOBAL'">
					<table class="keyvalue" cellspacing="0">
						<tr>
							<td class="key">{*LC__UNIVERSAL__TITLE*}:</td>
							<td class="value" style="font-weight:bold"><xsl:value-of select="cat_data/title"/></td>
						</tr>
						<tr>
							<td class="key">{*LC__CMDB__CATG__SYSID*}:</td>
							<td class="value" style="font-weight:bold"><xsl:value-of select="cat_data/sysid"/></td>
						</tr>
						<tr>
							<td class="key">{*LC__CMDB__CATG__PURPOSE*}:</td>
							<td class="value" style="font-weight:bold"><xsl:value-of select="cat_data/purpose"/></td>
						</tr>
						<tr>
							<td class="key">{*LC__CMDB__CATG__CATEGORY*}:</td>
							<td class="value"><xsl:value-of select="cat_data/category"/></td>
						</tr>
						<tr>
							<td class="key">{*LC__UNIVERSAL__CONDITION*}:</td>
							<td class="value"><xsl:value-of select="../../status/@lc_title"/></td>
						</tr>
                        <tr>
                            <td class="key">{*LC__CMDB__CATG__ODEP_OBJ*} ID:</td>
                            <td class="value"><xsl:value-of select="../../id" /></td>
                        </tr>
                         <tr>
                            <td class="key">{*LC__TASK__DETAIL__WORKORDER__CREATION_DATE*}:</td>
                            <td class="value"><xsl:value-of select="../../created" /></td>
                        </tr>
                        <tr>
                            <td class="key">{*LC__UNIVERSAL__DATE_OF_CHANGE*}:</td>
                            <td class="value"><xsl:value-of select="../../updated" /></td>
                        </tr>
                        <tr>
                            <td class="key">{*LC__UNIVERSAL__STATUS*}:</td>
                            <td class="value"><xsl:value-of select="cat_data/cmdb_status" /></td>
                        </tr>
						<tr>
							<td class="key" valign="top">{*LC__CMDB__CAT__COMMENTARY*}:</td>
							<td class="value">
								<xsl:call-template name="nl2br">
									<xsl:with-param name="text" select="cat_data/description" />
								</xsl:call-template>
							</td>
						</tr>
					</table>
				</xsl:when>

                <xsl:when test="@const = 'C__CATS__AC'">
					<table class="keyvalue" cellspacing="0">
						<tr>
							<td class="key"><xsl:value-of select="cat_data/type/@title"/>:</td>
							<td class="value" style="font-weight:bold"><xsl:value-of select="cat_data/type"/></td>
						</tr>
						<tr>
							<td class="key"><xsl:value-of select="cat_data/threshold/@title"/>:</td>
							<td class="value" style="font-weight:bold"><xsl:value-of select="cat_data/threshold"/>&#160;<xsl:value-of select="cat_data/threshold_unit"/></td>
						</tr>
						<tr>
							<td class="key"><xsl:value-of select="cat_data/capacity/@title"/>:</td>
							<td class="value" style="font-weight:bold"><xsl:value-of select="cat_data/capacity"/>&#160;<xsl:value-of select="cat_data/capacity_unit"/></td>
						</tr>
						<tr>
							<td class="key"><xsl:value-of select="cat_data/air_quantity/@title"/>:</td>
							<td class="value" style="font-weight:bold"><xsl:value-of select="cat_data/air_quantity"/>&#160;<xsl:value-of select="cat_data/air_quantity_unit"/></td>
						</tr>
						<tr>
							<td class="key"><xsl:value-of select="cat_data/width/@title"/>/<xsl:value-of select="cat_data/height/@title"/>/<xsl:value-of select="cat_data/depth/@title"/>:</td>
							<td class="value" style="font-weight:bold"><xsl:value-of select="cat_data/width"/>&#215;<xsl:value-of select="cat_data/height"/>&#215;<xsl:value-of select="cat_data/depth"/>&#160;<xsl:value-of select="cat_data/dimension_unit"/></td>
						</tr>
                        <tr>
							<td class="key" valign="top"><xsl:value-of select="cat_data/description/@title"/>:</td>
							<td class="value">
								<xsl:call-template name="nl2br">
									<xsl:with-param name="text" select="cat_data/description" />
								</xsl:call-template>
							</td>
						</tr>
					</table>
	        	</xsl:when>

                <xsl:when test="@const = 'C__CATS__EPS'">
					<table class="keyvalue" cellspacing="0">
						<tr>
							<td class="key"><xsl:value-of select="cat_data/type/@title"/>:</td>
							<td class="value" style="font-weight:bold"><xsl:value-of select="cat_data/type"/></td>
						</tr>
						<tr>
							<td class="key"><xsl:value-of select="cat_data/warmup_time/@title"/>:</td>
							<td class="value" style="font-weight:bold"><xsl:value-of select="cat_data/warmup_time"/>&#160;<xsl:value-of select="cat_data/warmup_time_unit"/></td>
						</tr>
						<tr>
							<td class="key"><xsl:value-of select="cat_data/fuel_tank/@title"/>:</td>
							<td class="value" style="font-weight:bold"><xsl:value-of select="cat_data/fuel_tank"/>&#160;<xsl:value-of select="cat_data/volume_unit"/></td>
						</tr>
						<tr>
							<td class="key"><xsl:value-of select="cat_data/autonomy_time/@title"/>:</td>
							<td class="value" style="font-weight:bold"><xsl:value-of select="cat_data/autonomy_time"/>&#160;<xsl:value-of select="cat_data/autonomy_time_unit"/></td>
						</tr>
                        <tr>
							<td class="key" valign="top"><xsl:value-of select="cat_data/description/@title"/>:</td>
							<td class="value">
								<xsl:call-template name="nl2br">
									<xsl:with-param name="text" select="cat_data/description" />
								</xsl:call-template>
							</td>
						</tr>
					</table>
	        	</xsl:when>

				<xsl:when test="@const = 'C__CATG__FORMFACTOR'">
					<table class="keyvalue" cellspacing="0">
						<tr>
							<td class="key">{*LC__CMDB__CATG__FORMFACTOR*}:</td>
							<td class="value" style="font-weight:bold"><xsl:value-of select="cat_data/formfactor"/></td>
						</tr>
						<tr>
							<td class="key">{*LC__CMDB__CATG__RACKUNITS*}:</td>
							<td class="value"><xsl:value-of select="cat_data/rackunits"/></td>
						</tr>
						<tr>
							<td class="key">{*LC__CMDB__CATG__FORMFACTOR_INSTALLATION_WIDTH*}:</td>
							<td class="value">
								<xsl:value-of select="cat_data/width"/>&#xa0;<xsl:value-of select="cat_data/unit"/>
							</td>
						</tr>
						<tr>
							<td class="key">{*LC__CMDB__CATG__FORMFACTOR_INSTALLATION_HEIGHT*}:</td>
							<td class="value">
								<xsl:value-of select="cat_data/height"/>&#xa0;<xsl:value-of select="cat_data/unit"/>
							</td>
						</tr>
						<tr>
							<td class="key">{*LC__CMDB__CATG__FORMFACTOR_INSTALLATION_DEPTH*}:</td>
							<td class="value">
								<xsl:value-of select="cat_data/depth"/>&#xa0;<xsl:value-of select="cat_data/unit"/>
							</td>
						</tr>
						<tr>
							<td class="key">{*LC__CMDB__CATG__FORMFACTOR_INSTALLATION_WEIGHT*}:</td>
							<td class="value">
								<xsl:value-of select="cat_data/weight"/>&#xa0;<xsl:value-of select="cat_data/weight_unit"/>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">{*LC__CMDB__CAT__COMMENTARY*}:</td>
							<td class="value">
								<xsl:call-template name="nl2br">
									<xsl:with-param name="text" select="cat_data/description" />
								</xsl:call-template>
							</td>
						</tr>
					</table>
				</xsl:when>

                <xsl:when test="@const = 'C__CATG__CLUSTER'">
					<table class="keyvalue" cellspacing="0">
						<tr>
							<td class="key">{*LC__CMDB__CATG__CLUSTER__QUORUM*}:</td>
							<td class="value" style="font-weight:bold"><xsl:value-of select="cat_data/quorum"/></td>
						</tr>
						<tr>
							<td class="key">{*LC__CMDB__CATG__CLUSTER__ADMINISTRATION_SERVICE*}:</td>
							<td class="value">
                                <xsl:for-each select="cat_data/administration_service/.">
                                    <xsl:value-of select="." /> <br/>
                                 </xsl:for-each>
                            </td>
						</tr>
                        <tr>
                            <td class="key">{*LC__CMDB__LOGBOOK__DESCRIPTION*}:</td>
                            <td class="value">
                                <xsl:value-of select="cat_data/description" />
                            </td>
                        </tr>
					</table>
				</xsl:when>
		    <xsl:when test="@const = 'C__CATS__LAYER2_NET'">
                <table class="keyvalue" cellspacing="0">
                    <tr>
                        <td class="key">{*LC__CMDB__CATS__LAYER2_ID*}:</td>
                        <td class="value" style="font-weight:bold">
                            <xsl:value-of select="cat_data/vlan_id"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="key">{*LC__CMDB__CATS__LAYER2_STANDARD_VLAN*}:</td>
                        <td class="value">
                            <xsl:value-of select="cat_data/standard" />
                        </td>
                    </tr>
                    <tr>
                        <td class="key">{*LC__CMDB__CATS__LAYER2_TYPE*}:</td>
                        <td class="value">
                            <xsl:value-of select="cat_data/type"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="key">{*LC__CMDB__CATS__LAYER2_SUBTYPE*}:</td>
                        <td class="value">
                            <xsl:value-of select="cat_data/subtype" />
                        </td>
                    </tr>
                    <tr>
                        <td class="key">{*LC__CMDB__CATS__LAYER2_IP_HELPER_ADDRESSES*}:</td>
                        <td class="value">
                            <table style="border: 1px solid grey" cellspacing="0">
                            <tr>
                                <td style="background-color: #E5E9FF">IP</td>
                                <td style="background-color: #E5E9FF">{*LC__CMDB__CATG__MEDICAL_DEVICE__TYPE*}</td>
                            </tr>
                            <xsl:for-each select="cat_data/ip_helper_addresses/sub_ip_helper_addresses">
                                <tr>
                                    <td><xsl:value-of select="./@ip" /></td>
                                    <td><xsl:value-of select="./@type" /></td>
                                </tr>
                            </xsl:for-each>
                            </table>
                        </td>
                    </tr>
                </table>
			</xsl:when>
                <xsl:when test="@const = 'C__CATG__CLUSTER_SERVICE'">
                    <table cellspacing="0" class="listing content_table">
                        <thead>
                                <th>{*LC__CMDB__OBJTYPE__CLUSTER_SERVICE*}</th>
                                <th>{*LC__CMDB__CATG__CLUSTER_SERVICE__HOST_ADDRESSES*}</th>
                                <th>{*LC__CMDB__CATG__CLUSTER_SERVICE__VOLUMES*}</th>
                                <th>{*LC__CMDB__CATG__CLUSTER_SERVICE__SHARES*}</th>
                                <th>{*LC__CMDB__CATG__CLUSTER_SERVICE__TYPE*}</th>
                                <th>{*LC__CMDB__CATG__CLUSTER_SERVICE__RUNS_ON*}</th>
                                <th>{*LC__CMDB__CATG__CLUSTER_SERVICE__DEFAULT_SERVER*}</th>
                        </thead>
                        <tbody>
                            <xsl:for-each select="cat_data">
                                <tr>
                                    <td><xsl:value-of select="cluster_service" /></td>
                                    <td>
                                        <xsl:for-each select="hostaddresses/sub_hostaddresses">
                                            <xsl:value-of select="." />
                                            <xsl:if test="./@hostname">
                                                (<xsl:value-of select="./@hostname" />)
                                            </xsl:if>
                                            <br/>
                                        </xsl:for-each>
                                    </td>
                                    <td>
                                        <xsl:for-each select="drives/sub_drives">
                                            <xsl:value-of select="." /> <br/>
                                        </xsl:for-each>
                                    </td>
                                    <td>
                                        <xsl:for-each select="shares/sub_shares">
                                            <xsl:value-of select="." /> <br/>
                                        </xsl:for-each>
                                    </td>
                                    <td><xsl:value-of select="type" /></td>
                                    <td>
                                        <xsl:for-each select="runs_on/sub_runs_on">
                                            <xsl:value-of select="." /> <br/>
                                        </xsl:for-each>
                                    </td>
                                    <td><xsl:value-of select="default_server" /></td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </xsl:when>
                <xsl:when test="@const = 'C__CATG__CLUSTER_MEMBERS'">
                    <table cellspacing="0" class="listing content_table">
                        <thead>
                            <th>{*LC__UNIVERSAL__TITLE*}</th>
                            <th>{*LC__CMDB__OBJTYPE*}</th>
                        </thead>
                        <tbody>
                            <xsl:for-each select="cat_data">
                                <tr>
                                    <td><xsl:value-of select="member" /></td>
                                    <td><xsl:value-of select="member/@type_title" /></td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__SHARES'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__CLUSTER_MEMBERSHIPS'">
					<table cellspacing="0" class="listing content_table">
                        <thead>
                                <th>{*LC__CMDB__CATG__CLUSTER*}</th>
                                <th>{*LC__CMDB__OBJTYPE*}</th>
                        </thead>
                        <tbody>
                            <xsl:for-each select="cat_data">
                                <tr>
                                    <td><xsl:value-of select="connected_object" /></td>
                                    <td><xsl:value-of select="connected_object/@type_title" /></td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
					</table>
				</xsl:when>

				<xsl:when test="@const = 'C__CATG__ACCOUNTING'">
					<table class="keyvalue" cellspacing="0">
						<tr>
							<td class="key">{*LC__CMDB__CATG__ACCOUNTING_INVENTORY_NO*}:</td>
							<td class="value"><xsl:value-of select="cat_data/inventory_no"/></td>
						</tr>
						<tr>
							<td class="key">{*LC__CMDB__CATG__ACCOUNTING_ACCOUNT*}:</td>
							<td class="value"><xsl:value-of select="cat_data/account"/></td>
						</tr>
						<tr>
							<td class="key">{*LC__CMDB__CATG__ACCOUNTING_ORDER_DATE*}:</td>
							<td class="value"><xsl:value-of select="cat_data/order_date"/></td>
						</tr>
                        <tr>
							<td class="key">{*LC__CMDB__CATG__ACCOUNTING_DELIVERY_DATE*}:</td>
							<td class="value"><xsl:value-of select="cat_data/delivery_date"/></td>
						</tr>
                        <tr>
							<td class="key">{*LC__CMDB__CATG__ACCOUNTING_DATE_OF_INVOICE*}:</td>
							<td class="value"><xsl:value-of select="cat_data/acquirementdate"/></td>
						</tr>
						<tr>
							<td class="key">{*LC__CMDB__CATG__GLOBAL_PURCHASED_AT*}:</td>
							<td class="value">
								<xsl:for-each select="cat_data/contact">
									<xsl:choose>
										<xsl:when test="@type = 1">
											<xsl:value-of select="@firstname"/>&#xa0;<xsl:value-of select="@lastname"/>
										</xsl:when>
										<xsl:otherwise>
											<xsl:value-of select="." />
										</xsl:otherwise>
									</xsl:choose>
									<xsl:if test="position() != last()">, </xsl:if>
								</xsl:for-each>
							</td>
						</tr>
						<tr>
							<td class="key">{*LC__CMDB__CATG__GLOBAL_PRICE*}:</td>
							<td class="value"><xsl:value-of select="cat_data/price"/></td>
						</tr>
                        <tr>
                            <td class="key">{*LC__CMDB__CATG__ACCOUNTING__OPERATION_EXPENSE*}:</td>
                            <td class="value"><xsl:value-of select="cat_data/operation_expense"/>&#xa0;<xsl:value-of select="cat_data/operation_expense_interval"/></td>
                        </tr>
                        <tr>
                            <td class="key">{*LC__CMDB__CATG__ACCOUNTING_COST_UNIT*}:</td>
                            <td class="value"><xsl:value-of select="cat_data/cost_unit"/></td>
                        </tr>
                        <tr>
                            <td class="key">{*LC__CMDB__CATG__GLOBAL_ORDER_NO*}:</td>
                            <td class="value"><xsl:value-of select="cat_data/order_no"/></td>
                        </tr>
						<tr>
							<td class="key">{*LC__CMDB__CATG__ACCOUNTING_DELIVERY_NOTE_NO*}:</td>
							<td class="value"><xsl:value-of select="cat_data/delivery_note_no"/></td>
						</tr>
						<tr>
							<td class="key">{*LC__CMDB__CATG__GLOBAL_INVOICE_NO*}:</td>
							<td class="value"><xsl:value-of select="cat_data/invoice_no"/></td>
						</tr>
						<tr>
							<td class="key">{*LC__CMDB__CATG__ACCOUNTING_PROCUREMENT*}:</td>
							<td class="value"><xsl:value-of select="cat_data/procurement"/></td>
						</tr>
						<tr>
							<td class="key">{*LC__CMDB__CATG__GLOBAL_GUARANTEE_PERIOD*}:</td>
							<td class="value"><xsl:value-of select="cat_data/guarantee_period_base"/>&#xa0;<xsl:value-of select="cat_data/guarantee_period"/>&#xa0;<xsl:value-of select="cat_data/guarantee_period_unit"/></td>
						</tr>

						<tr>
							<td class="key">{*LC__CMDB__CATG__GLOBAL_GUARANTEE_STATUS*}:</td>
							<td class="value">
								<xsl:value-of select="cat_data/guarantee_period_status"/>
							</td>
						</tr>
                        <tr>
                            <td class="key">{*LC__CMDB__CAT__COMMENTARY*}:</td>
                            <td class="value">
                                <xsl:call-template name="nl2br">
									<xsl:with-param name="text" select="cat_data/description" />
								</xsl:call-template>
                            </td>
                        </tr>
					</table>
				</xsl:when>

				<xsl:when test="@const = 'C__CATG__CONTACT'">
					<table cellspacing="0" class="listing content_table">
		        		<thead>
			        		<th>{*LC__CATG__CONTACT_LIST__NAME*}</th>
                            <th>{*LC__CATG__CONTACT_LIST__TYPE*}</th>
                            <th>{*LC__CONTACT__PERSON_DEPARTMENT*}</th>
                            <th>{*LC__CONTACT__PERSON_MAIL_ADDRESS*}</th>
                            <th>{*LC__CATG__CONTACT_LIST__PHONE*}</th>
                            <th>{*LC__CATG__CONTACT_LIST__ASSIGNED_ORGANISATION*}</th>
                            <th>{*LC__CMDB__CONTACT_ROLE*}</th>
                            <th>{*LC__CATG__CONTACT_LIST__PRIMARY*}</th>
		        		</thead>
		        		<tbody>
			        	<xsl:for-each select="cat_data">
			        		<tr>
                                <td><xsl:value-of select="contact/."/></td>
                                <td>
                                    <xsl:choose>
                                        <xsl:when test="contact_object/@type = 'C__OBJTYPE__PERSON'">
                                            <img alt="Person" src="%%BASE_URL%%images/icons/contact/person_intern.gif"/>
                                        </xsl:when>
                                        <xsl:when test="contact_object/@type = 'C__OBJTYPE__PERSON_GROUP'">
                                            <img alt="Person" src="%%BASE_URL%%images/icons/contact/group.gif"/>
                                        </xsl:when>
                                        <xsl:when test="contact_object/@type = 'C__OBJTYPE__ORGANIZATION'">
                                            <img alt="Person" src="%%BASE_URL%%images/icons/silk/sitemap.png"/>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <img alt="Person" src="%%BASE_URL%%images/dtree/page.gif"/>
                                        </xsl:otherwise>
                                    </xsl:choose>

                                    &#xa0;<xsl:value-of select="contact_object/@type_title" />
                                </td>
                                <td><xsl:value-of select="contact/@department" /></td>
                                <td>
                                    <xsl:value-of select="contact/@mail"/>
                                    <xsl:value-of select="contact/@email_address"/>
                                    <xsl:value-of select="contact/@mail_address"/>
                                </td>
                                <td>
                                    <xsl:choose>
                                        <xsl:when test="contact/@phone_company != ''">
                                            {*LC__CONTACT__PERSON_TELEPHONE_COMPANY*}: <b><xsl:value-of select="contact/@phone_company" /></b>
                                        </xsl:when>
                                        <xsl:when test="contact/@phone_mobile != ''">
                                            {*LC__CONTACT__PERSON_TELEPHONE_MOBILE*}: <b><xsl:value-of select="contact/@phone_mobile" /></b>
                                        </xsl:when>
                                        <xsl:when test="contact/@phone_home != ''">
                                            {*LC__CONTACT__PERSON_TELEPHONE_HOME*}: <b><xsl:value-of select="contact/@phone_home" /></b>
                                        </xsl:when>
                                        <xsl:when test="contact/@phone != ''">
                                            <xsl:value-of select="contact/@phone" />
                                        </xsl:when>
                                        <xsl:when test="contact/@telephone != ''">
                                            <xsl:value-of select="contact/@telephone" />
                                        </xsl:when>
                                    </xsl:choose>
                                </td>
                                <td><xsl:value-of select="contact/sub_contact/@company_title"/></td>
                                <td><xsl:value-of select="role"/></td>
                                <td>
                                    <xsl:choose>
                                        <xsl:when test="primary">
                                            <xsl:value-of select="primary"/>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            0
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </td>
				        	</tr>
				        </xsl:for-each>
			        	</tbody>
			        </table>
				</xsl:when>

				<xsl:when test="@const = 'C__CATG__LOCATION'">
					<p style="margin:5px 5px 5px 10px;">
					<xsl:call-template name="location">
			          	<xsl:with-param name="loc" select="../../id" />
			        </xsl:call-template>
			        </p>
				</xsl:when>

				<xsl:when test="@const = 'C__CATG__ACCESS'">
					<table cellspacing="0" class="listing content_table">
						<thead>
		        			<th>{*LC__CMDB__CATG__MODEL_TITLE*}</th>
		        			<th>{*LC__CMDB__CATG__ACCESS_TYPE*}</th>
		        			<th>Host[:Port] / URL</th>
		        		</thead>
		        		<tbody>
		        		<xsl:for-each select="cat_data">
		        			<xsl:choose>
								<xsl:when test="primary/@value = '1'">
									<tr class="primary">
										<td><xsl:value-of select="title"/></td>
					        			<td><xsl:value-of select="type"/></td>
					        			<td><a href="{formatted_url}"><xsl:value-of select="formatted_url"/></a></td>
					        		</tr>
								</xsl:when>
								<xsl:otherwise>
			        				<tr>
			        					<td><xsl:value-of select="title"/></td>
					        			<td><xsl:value-of select="type"/></td>
					        			<td><a href="{formated_url}"><xsl:value-of select="formatted_url"/></a></td>
					        		</tr>
			        			</xsl:otherwise>
			        		</xsl:choose>
		        		</xsl:for-each>
		        		</tbody>
		        	</table>
				</xsl:when>

		        <xsl:when test="@const = 'C__CATG__IP'">

					<xsl:variable name="ipv4_const">C__CATS_NET_TYPE__IPV4</xsl:variable>
					<xsl:variable name="ipv6_const">C__CATS_NET_TYPE__IPV6</xsl:variable>

		        	<table cellspacing="0" class="listing content_table">
		        		<thead>
		        			<th>{*LC__CMDB__CATS__NET__TYPE*}</th>
                            <th>{*LC__CATP__IP__ADDRESS*}</th>
		        			<th>{*LC__CATP__IP__ASSIGN*}</th>
		        			<th>{*LC__CATP__IP__HOSTNAME_FQDN*}</th>
                            <th>{*LC__CATG__IP__ASSIGNED_NET*}</th>
                            <th>{*LC__CMDB__CATG__NETWORK__PRIM_IP_BOOL*}</th>
		        			<th>{*LC__CATP__IP__ACTIVE*}</th>
                            <th>{*LC__CATP__IP__DNSDOMAIN*}</th>
	        			</thead>
	        			<tbody>
			        		<xsl:for-each select="cat_data">
			        			<xsl:choose>
									<xsl:when test="primary/@value = '1'">
				        				<tr class="primary">
				        					<td><xsl:value-of select="net_type"/></td>
                                            <td>
                                                <xsl:choose>
                                                    <xsl:when test="net_type/@const = $ipv4_const">
                                                        <xsl:value-of select="ipv4_address/@ref_title"/>
                                                    </xsl:when>
                                                    <xsl:otherwise>
                                                        <xsl:value-of select="ipv6_address/@ref_title"/>
                                                    </xsl:otherwise>
                                                </xsl:choose>
                                            </td>
						        			<td>
												<xsl:choose>
													<xsl:when test="net_type/@const = $ipv4_const">
														<xsl:value-of select="ipv4_assignment"/>
													</xsl:when>
													<xsl:otherwise>
														<xsl:value-of select="ipv6_assignment"/>
													</xsl:otherwise>
												</xsl:choose>
											</td>
						        			<td><xsl:value-of select="primary_fqdn"/></td>
                                            <td><xsl:value-of select="net"/></td>
                                            <td><xsl:value-of select="primary"/></td>
						        			<td><xsl:value-of select="active"/></td>
                                            <td><xsl:value-of select="dns_domain"/></td>
						        		</tr>
						        	</xsl:when>
				        			<xsl:otherwise>
				        				<tr>
				        					<td><xsl:value-of select="net_type"/></td>
                                            <td>
                                                <xsl:choose>
                                                    <xsl:when test="net_type/@const = $ipv4_const">
                                                        <xsl:value-of select="ipv4_address/@ref_title"/>
                                                    </xsl:when>
                                                    <xsl:otherwise>
                                                        <xsl:value-of select="ipv6_address/@ref_title"/>
                                                    </xsl:otherwise>
                                                </xsl:choose>
                                            </td>
						        			<td>
												<xsl:choose>
													<xsl:when test="net_type/@const = $ipv4_const">
														<xsl:value-of select="ipv4_assignment"/>
													</xsl:when>
													<xsl:otherwise>
														<xsl:value-of select="ipv6_assignment"/>
													</xsl:otherwise>
												</xsl:choose>
											</td>
						        			<td><xsl:value-of select="primary_fqdn"/></td>
                                            <td><xsl:value-of select="net"/></td>
                                            <td><xsl:value-of select="primary"/></td>
						        			<td><xsl:value-of select="active"/></td>
                                            <td><xsl:value-of select="dns_domain"/></td>
						        		</tr>
				        			</xsl:otherwise>
				        		</xsl:choose>

				        	</xsl:for-each>
				        </tbody>
		        	</table>
		        </xsl:when>

		        <xsl:when test="@const = 'C__CMDB__SUBCAT__NETWORK_PORT'">
		        	<xsl:for-each select="cat_data">
			        	<fieldset>
			        		<legend><strong><xsl:value-of select="title"/></strong></legend>
			        		<table cellspacing="0" class="listing content_table">
                                <tr>
				        			<td>{*LC__CATD__SANPOOL_TITLE*}:</td>
				        			<td><xsl:value-of select="title"/></td>
				        		</tr>
                                <tr>
				        			<td>{*LC__CMDB__CATG__PORT__CON_INTERFACE*}:</td>
				        			<td>
                                        <xsl:value-of select="interface"/>
                                        <xsl:value-of select="hba"/>
                                    </td>
				        		</tr>
				        		<tr>
				        			<td>{*LC__CMDB__CATG__PORT__TYPE*}:</td>
				        			<td><xsl:value-of select="port_type"/></td>
				        		</tr>
				        		<tr>
				        			<td>{*LC__CMDB__CATG__PORT__MODE*}:</td>
				        			<td><xsl:value-of select="port_mode"/></td>
				        		</tr>
				        		<tr>
				        			<td>{*LC__CMDB__CATG__PORT__PLUG*}:</td>
				        			<td><xsl:value-of select="plug_type"/></td>
				        		</tr>

								<tr>
									<td>{*LC__CMDB__CATG__PORT__NEGOTIATION*}:
									</td>
									<td><xsl:value-of select="negotiation"/>
									</td>
								</tr>
								<tr>
									<td>{*LC__CMDB__CATG__PORT__DUPLEX*}:
									</td>
									<td><xsl:value-of select="duplex"/>
									</td>
								</tr>
								<tr>
									<td>{*LC__CMDB__CATG__PORT__SPEED*}:
									</td>
									<td><xsl:value-of select="speed"/>&#xa0;<xsl:value-of select="speed_type"/>
									</td>
								</tr>
								<tr>
									<td>Standard:
									</td>
									<td><xsl:value-of select="standard"/>
									</td>
								</tr>
								<tr>
				        			<td>{*LC__CMDB__CATG__PORT__MAC*}:</td>
				        			<td><xsl:value-of select="mac"/></td>
				        		</tr>
								<tr>
									<td>{*LC__CMDB__CONNECTED_WITH*}:
									</td>
									<td>
										<xsl:value-of select="assigned_connector/sub_assigned_connector"/> &gt; <xsl:value-of select="assigned_connector/sub_assigned_connector/@name"/>
									</td>
								</tr>
								<tr>
				        			<td>{*LC__CMDB__CATG__PORT__ACTIVE*}:</td>
				        			<td>
				        				<xsl:choose>
					        				<xsl:when test="active = '1'">
					        					{*LC__UNIVERSAL__YES*}
					        				</xsl:when>
					        				<xsl:otherwise>
					        					{*LC__UNIVERSAL__NO*}
						        			</xsl:otherwise>
						        		</xsl:choose>
				        			</td>
				        		</tr>
				        		<tr>
				        			<td>{*LC__CATG__IP_ADDRESS*}:</td>
				        			<td>
				        				<xsl:apply-templates select="addresses"/>
				        			</td>
				        		</tr>
								<tr>
				        			<td>{*LC__CMDB__LAYER2_NET*}:</td>
				        			<td>
				        				<xsl:for-each select="layer2_assignment/sub_layer2_assignment">
											<xsl:value-of select="./@title" /><br />
										</xsl:for-each>
				        			</td>
				        		</tr>
			        		</table>
			        	</fieldset>
			        </xsl:for-each>
		        </xsl:when>

		        <xsl:when test="@const = 'C__CATG__NETWORK_PORT'">
		        	<xsl:for-each select="cat_data">
			        	<fieldset>
			        		<legend><strong><xsl:value-of select="title"/></strong></legend>
			        		<table cellspacing="0" class="listing content_table">
                                <tr>
				        			<td>{*LC__CATD__SANPOOL_TITLE*}:</td>
				        			<td><xsl:value-of select="title"/></td>
				        		</tr>
                                <tr>
				        			<td>{*LC__CMDB__CATG__PORT__CON_INTERFACE*}:</td>
				        			<td>
                                        <xsl:value-of select="interface"/>
                                        <xsl:value-of select="hba"/>
                                    </td>
				        		</tr>
				        		<tr>
				        			<td>{*LC__CMDB__CATG__PORT__TYPE*}:</td>
				        			<td><xsl:value-of select="port_type"/></td>
				        		</tr>
				        		<tr>
				        			<td>{*LC__CMDB__CATG__PORT__MODE*}:</td>
				        			<td><xsl:value-of select="port_mode"/></td>
				        		</tr>
				        		<tr>
				        			<td>{*LC__CMDB__CATG__PORT__PLUG*}:</td>
				        			<td><xsl:value-of select="plug_type"/></td>
				        		</tr>

								<tr>
									<td>{*LC__CMDB__CATG__PORT__NEGOTIATION*}:
									</td>
									<td><xsl:value-of select="negotiation"/>
									</td>
								</tr>
								<tr>
									<td>{*LC__CMDB__CATG__PORT__DUPLEX*}:
									</td>
									<td><xsl:value-of select="duplex"/>
									</td>
								</tr>
								<tr>
									<td>{*LC__CMDB__CATG__PORT__SPEED*}:
									</td>
									<td><xsl:value-of select="speed"/>&#xa0;<xsl:value-of select="speed_type"/>
									</td>
								</tr>
								<tr>
									<td>Standard:
									</td>
									<td><xsl:value-of select="standard"/>
									</td>
								</tr>
								<tr>
				        			<td>{*LC__CMDB__CATG__PORT__MAC*}:</td>
				        			<td><xsl:value-of select="mac"/></td>
				        		</tr>
								<tr>
									<td>{*LC__CMDB__CONNECTED_WITH*}:
									</td>
									<td>
										<xsl:value-of select="assigned_connector/sub_assigned_connector"/> &gt; <xsl:value-of select="assigned_connector/sub_assigned_connector/@name"/>
									</td>
								</tr>
								<tr>
				        			<td>{*LC__CMDB__CATG__PORT__ACTIVE*}:</td>
				        			<td>
				        				<xsl:choose>
					        				<xsl:when test="active = '1'">
					        					{*LC__UNIVERSAL__YES*}
					        				</xsl:when>
					        				<xsl:otherwise>
					        					{*LC__UNIVERSAL__NO*}
						        			</xsl:otherwise>
						        		</xsl:choose>
				        			</td>
				        		</tr>
				        		<tr>
				        			<td>{*LC__CATG__IP_ADDRESS*}:</td>
				        			<td>
				        				<xsl:apply-templates select="addresses"/>
				        			</td>
				        		</tr>
								<tr>
				        			<td>{*LC__CMDB__LAYER2_NET*}:</td>
				        			<td>
				        				<xsl:for-each select="layer2_assignment/sub_layer2_assignment">
											<xsl:value-of select="./@title" /><br />
										</xsl:for-each>
				        			</td>
				        		</tr>
			        		</table>
			        	</fieldset>
			        </xsl:for-each>
		        </xsl:when>

		        <xsl:when test="@const = 'C__CMDB__SUBCAT__NETWORK_INTERFACE_L'">
		        	<table cellspacing="0" class="listing content_table">
		        		<thead>
		        			<th>{*LC__CMDB__CATG__INTERFACE_L__TITLE*}</th>
		        			<th>{*LC__CMDB__CATG__INTERFACE_L__TYPE*}</th>
		        			<th>{*LC__CMDB__CATG__INTERFACE_L__NET*}</th>
							<th>{*LC__CMDB__CATG__NETWORK__MAC*}</th>
		        			<th>{*LC__CMDB__CATG__INTERFACE_L__STANDARD*}</th>
		        			<th>{*LC__CMDB__CATG__INTERFACE_L__ACTIVE*}</th>
                            <th>Port {*LC__UNIVERSAL__ASSIGNMENT*}</th>
                            <th>{*LC__CATG__IP_ADDRESS*}</th>
	        			</thead>
	        			<tbody>
			        		<xsl:for-each select="cat_data">
		        				<tr>
		        					<td><xsl:value-of select="title"/></td>
				        			<td><xsl:value-of select="type"/></td>
				        			<td>
										<xsl:for-each select="net/sub_net">
											<xsl:value-of select="./@title" /><br />
										</xsl:for-each>
									</td>
									<td><xsl:value-of select="mac"/></td>
				        			<td><xsl:value-of select="standard"/></td>
				        			<td>
					        			<xsl:choose>
					        				<xsl:when test="active/@value = '1'">
					        					{*LC__UNIVERSAL__YES*}
					        				</xsl:when>
					        				<xsl:otherwise>
					        					{*LC__UNIVERSAL__NO*}
					        				</xsl:otherwise>
					        			</xsl:choose>
					        		</td>
                                    <td>
										<xsl:for-each select="ports/sub_ports">
											<xsl:value-of select="." /><br/>
										</xsl:for-each>
                                    </td>
                                    <td>
										<xsl:for-each select="addresses/sub_addresses">
											<xsl:value-of select="." />
											<xsl:if test="./@hostname">
												&#xa0;(<xsl:value-of select="./@hostname" />)
											</xsl:if><br/>
										</xsl:for-each>
									</td>
				        		</tr>
				        	</xsl:for-each>
				        </tbody>
		        	</table>
		        </xsl:when>

		        <xsl:when test="@const = 'C__CATG__NETWORK_LOG_PORT'">
		        	<table cellspacing="0" class="listing content_table">
		        		<thead>
		        			<th>{*LC__CMDB__CATG__INTERFACE_L__TITLE*}</th>
		        			<th>{*LC__CMDB__CATG__INTERFACE_L__TYPE*}</th>
		        			<th>{*LC__CMDB__CATG__INTERFACE_L__NET*}</th>
							<th>{*LC__CMDB__CATG__NETWORK__MAC*}</th>
		        			<th>{*LC__CMDB__CATG__INTERFACE_L__STANDARD*}</th>
		        			<th>{*LC__CMDB__CATG__INTERFACE_L__ACTIVE*}</th>
                            <th>Port {*LC__UNIVERSAL__ASSIGNMENT*}</th>
                            <th>{*LC__CATG__IP_ADDRESS*}</th>
	        			</thead>
	        			<tbody>
			        		<xsl:for-each select="cat_data">
		        				<tr>
		        					<td><xsl:value-of select="title"/></td>
				        			<td><xsl:value-of select="type"/></td>
				        			<td>
										<xsl:for-each select="net/sub_net">
											<xsl:value-of select="./@title" /><br />
										</xsl:for-each>
									</td>
									<td><xsl:value-of select="mac"/></td>
				        			<td><xsl:value-of select="standard"/></td>
				        			<td>
					        			<xsl:choose>
					        				<xsl:when test="active/@value = '1'">
					        					{*LC__UNIVERSAL__YES*}
					        				</xsl:when>
					        				<xsl:otherwise>
					        					{*LC__UNIVERSAL__NO*}
					        				</xsl:otherwise>
					        			</xsl:choose>
					        		</td>
                                    <td>
										<xsl:for-each select="ports/sub_ports">
											<xsl:value-of select="." /><br/>
										</xsl:for-each>
                                    </td>
                                    <td>
										<xsl:for-each select="addresses/sub_addresses">
											<xsl:value-of select="." />
											<xsl:if test="./@hostname">
												&#xa0;(<xsl:value-of select="./@hostname" />)
											</xsl:if><br/>
										</xsl:for-each>
									</td>
				        		</tr>
				        	</xsl:for-each>
				        </tbody>
		        	</table>
		        </xsl:when>

		        <xsl:when test="@const = 'C__CATG__DRIVE'">
		        	<table cellspacing="0" class="listing content_table">
		        		<thead>
		        			<th>{*LC__CATD__DRIVE_TYPE*}</th>
		        			<th>{*LC__CATD__DRIVE_LETTER*}</th>
							<th>{*LC__CMDB__CATG__DRIVE__SYSTEM_DRIVE*}</th>
		        			<th>{*LC__CATD__DRIVE_TITLE*}</th>
		        			<th>{*LC__CATD__DRIVE_FILESYSTEM*}</th>
		        			<th>{*LC__CATD__DRIVE_CAPACITY*}</th>
							<th>{*LC__CATD__DRIVE_DEVICE*}</th>
							<th>{*LC__CATD_DRIVE_TYPE__RAID_GROUP*}</th>
	        			</thead>
	        			<tbody>
			        		<xsl:for-each select="cat_data">
		        				<tr>
		        					<td><xsl:value-of select="drive_type"/></td>
				        			<td><xsl:value-of select="mount_point"/></td>
									<td><xsl:value-of select="system_drive"/></td>
				        			<td><xsl:value-of select="title"/></td>
				        			<td><xsl:value-of select="filesystem"/></td>
				        			<td><xsl:value-of select="capacity"/>&#xa0;<xsl:value-of select="unit"/></td>
									<td><xsl:value-of select="device"/></td>
									<td><xsl:value-of select="assigned_raid"/></td>
				        		</tr>
				        	</xsl:for-each>
				        </tbody>
		        	</table>
		        </xsl:when>

                <xsl:when test="@const = 'C__CATG__STORAGE_DEVICE'">
                    <table cellspacing="0" class="listing content_table">
                        <thead>
                            <th>{*LC__CMDB__CATG__TITLE*}</th>
                            <th>{*LC__CATG__STORAGE_TYPE*}</th>
                            <th>{*LC__CATG__STORAGE_MANUFACTURER*}</th>
                            <th>{*LC__CATG__STORAGE_MODEL*}</th>
                            <th>{*LC__CATG__STORAGE_CAPACITY*}</th>
                            <th>{*LC__CATG__STORAGE_HOTSPARE*}</th>
                            <th>{*LC__CATG__STORAGE_CONNECTION_TYPE*}</th>
                            <th>{*LC__CATG__STORAGE_CONTROLLER*}</th>
                        </thead>
                        <tbody>
                            <xsl:for-each select="cat_data">
                                <tr>
                                    <td><xsl:value-of select="title"/></td>
                                    <td><xsl:value-of select="type"/></td>
                                    <td><xsl:value-of select="manufacturer"/></td>
                                    <td><xsl:value-of select="model"/></td>
                                    <td><xsl:value-of select="capacity"/>&#xa0;<xsl:value-of select="unit"/></td>
                                    <td><xsl:value-of select="hotspare"/></td>
                                    <td><xsl:value-of select="connected"/></td>
                                    <td><xsl:value-of select="controller"/></td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </xsl:when>

		        <xsl:when test="@const = 'C__CMDB__SUBCAT__STORAGE__DEVICE'">
		        	<table cellspacing="0" class="listing content_table">
		        		<thead>
		        			<th>{*LC__CMDB__CATG__TITLE*}</th>
                            <th>{*LC__CATG__STORAGE_TYPE*}</th>
		        			<th>{*LC__CATG__STORAGE_MANUFACTURER*}</th>
		        			<th>{*LC__CATG__STORAGE_MODEL*}</th>
		        			<th>{*LC__CATG__STORAGE_CAPACITY*}</th>
		        			<th>{*LC__CATG__STORAGE_HOTSPARE*}</th>
		        			<th>{*LC__CATG__STORAGE_CONNECTION_TYPE*}</th>
                            <th>{*LC__CATG__STORAGE_CONTROLLER*}</th>
	        			</thead>
	        			<tbody>
			        		<xsl:for-each select="cat_data">
		        				<tr>
		        					<td><xsl:value-of select="title"/></td>
                                    <td><xsl:value-of select="type"/></td>
				        			<td><xsl:value-of select="manufacturer"/></td>
				        			<td><xsl:value-of select="model"/></td>
				        			<td><xsl:value-of select="capacity"/>&#xa0;<xsl:value-of select="unit"/></td>
				        			<td><xsl:value-of select="hotspare"/></td>
				        			<td><xsl:value-of select="connected"/></td>
                                    <td><xsl:value-of select="controller"/></td>
				        		</tr>
				        	</xsl:for-each>
				        </tbody>
		        	</table>
		        </xsl:when>

		        <xsl:when test="@const = 'C__CATG__CONTROLLER_FC_PORT'">
		        	<table cellspacing="0" class="listing content_table">
		        		<thead>
		        			<th>{*LC__CATG__CONTROLLER_FC_PORT_TITLE*}</th>
		        			<th>{*LC__CATG__CONTROLLER_FC_PORT_TYPE*}</th>
		        			<th>{*LC__CATG__CONTROLLER_FC_PORT_MEDIUM*}</th>
                            <th>{*LC__CMDB__CATG__PORT__SPEED*}</th>
		        			<th>{*LC__CATG__CONTROLLER_FC_PORT_NODE_WWN*}</th>
                            <th>{*LC__CATG__CONTROLLER_FC_PORT_PORT_WWN*}</th>
                            <th>{*LC__CMDB__CATG__POWER_CONSUMER_CONNECTION*}</th>
                            <th>{*LC__CMDB__CATG__FC_PORT__SAN_ZONING*}</th>
                            <th>{*LC__CATG__CONTROLLER_FC_CONTROLLER*}</th>
	        			</thead>
	        			<tbody>
			        		<xsl:for-each select="cat_data">
		        				<tr>
		        					<td><xsl:value-of select="title"/></td>
				        			<td><xsl:value-of select="type"/></td>
				        			<td><xsl:value-of select="medium"/></td>
                                    <td><xsl:value-of select="speed"/>&#xa0;<xsl:value-of select="speed_type"/></td>
				        			<td><xsl:value-of select="wwn"/></td>
				        			<td><xsl:value-of select="wwpn"/></td>
                                    <td>
                                        <xsl:choose>
                                            <xsl:when test="assigned_connector != ''">
                                                <xsl:value-of select="assigned_connector"/> -> <xsl:value-of select="assigned_connector/sub_assigned_connector/@name"/>
                                            </xsl:when>
                                            <xsl:otherwise>
                                                tests
                                            </xsl:otherwise>
                                        </xsl:choose>
                                    </td>
                                    <td>
                                        <xsl:for-each select="san_zones/sub_san_zones">
                                            <xsl:value-of select="." /> <br/>
                                         </xsl:for-each>
                                    </td>
                                    <td><xsl:value-of select="connected_controller"/></td>
				        		</tr>
				        	</xsl:for-each>
				        </tbody>
		        	</table>
		        </xsl:when>

		        <xsl:when test="@const = 'C__CATG__DEPENDENCY'">

		        	<table cellspacing="0" class="listing content_table">
		        		<thead>
			        		<th><img src="%%BASE_URL%%images/icons/addiction_right.png"/></th>
			        		<th><img src="%%BASE_URL%%images/icons/addiction_left.png"/></th>
		        		</thead>
		        		<tbody>
			        	<xsl:for-each select="cat_data">

		        			<xsl:variable name="master"><xsl:value-of select="master/@id"/></xsl:variable>
			        		<xsl:variable name="slave"><xsl:value-of select="slave/@id"/></xsl:variable>

			        		<tr>

				        		<xsl:choose>
				        			<xsl:when test="../../../id = $master">
				        				<td><xsl:value-of select="slave"/></td><td></td>
				        			</xsl:when>
				        			<xsl:when test="../../../id = $slave">
				        				<td></td><td><xsl:value-of select="master"/></td>
				        			</xsl:when>
				        		</xsl:choose>

				        	</tr>
				        </xsl:for-each>
			        	</tbody>
			        </table>

	        	</xsl:when>

	        	<xsl:when test="@const = 'C__CATG__APPLICATION'">
	        		<table cellspacing="0" class="listing listing content_table">
	        			<thead>
		        			<th>{*LC__CMDB__CATG__APPLICATION_OBJ_APPLICATION*}</th>
							<th>{*LC__CMDB__CATG__LIC_ASSIGN__LICENSE*}</th>
                            <th>{*LC__CMDB__CATS__LICENCE_KEY*}</th>
							<th>{*LC__CMDB__CATS__DATABASE_SCHEMA*}</th>
							<th>{*LC__CMDB__CATG__IT_SERVICE*}</th>
                            <th>{*LC__CMDB__CATS__APPLICATION_VARIANT__VARIANT*}</th>
		        			<th>{*LC__CMDB__CATG__DESCRIPTION*}</th>
	        			</thead>
	        			<tbody>
		        		<xsl:for-each select="cat_data">
			        		<xsl:variable name="app"><xsl:value-of select="application/@id"/></xsl:variable>
			        		<tr>
			        			<td><xsl:value-of select="application"/></td>
								<td><xsl:value-of select="assigned_license"/></td>
								<td><xsl:value-of select="assigned_license/sub_assigned_license/@key"/></td>
                                <td><xsl:value-of select="assigned_database_schema"/></td>
								<td>
                                    <xsl:for-each select="assigned_it_service/sub_assigned_it_service">
                                        <xsl:value-of select="." /><br/>
                                    </xsl:for-each>
                                </td>
                                <td><xsl:value-of select="assigned_variant/@variant"/></td>
			        			<td><xsl:value-of select="description"/></td>
			        		</tr>
		        		</xsl:for-each>
		        		</tbody>
		        	</table>
	        	</xsl:when>

	        	<xsl:when test="@const = 'C__CATG__LDEV_SERVER'">
                    <table cellspacing="0" class="listing listing content_table">
	        			<thead>
		        			<th>{*LC__CATG__RAID_TITLE*}</th>
		        			<th>{*LC__CATD__SANPOOL_LUN*}</th>
		        			<th>{*LC__CATD__SANPOOL_SEGMENT_SIZE*}</th>
		        			<th>{*LC__CATD__SANPOOL_CAPACITY*}</th>
                            <th>{*LC__CATD__SANPOOL_DEVICES*}</th>
                            <th>{*LC__UNIVERSAL__PATHS*}</th>
                            <th>{*LC__CMDB__CATG__LDEV_CLIENT*}</th>
	        			</thead>
	        			<tbody>
		        		<xsl:for-each select="cat_data">
			        		<tr>
			        			<td><xsl:value-of select="title"/></td>
			        			<td><xsl:value-of select="lun"/></td>
			        			<td><xsl:value-of select="segment_size"/> kB</td>
			        			<td><xsl:value-of select="capacity"/>&#xa0;<xsl:value-of select="unit"/></td>
                                <td>
                                     <xsl:for-each select="connected_devices/sub_connected_devices">
                                        <xsl:value-of select="." /><br/>
                                    </xsl:for-each>
                                </td>
                                <td>
                                    <xsl:for-each select="paths/sub_paths" >
                                        <xsl:value-of select="." /><br/>
                                     </xsl:for-each >
                                </td>
                                <td>
                                    <xsl:for-each select="ldev_clients/sub_ldev_clients">
                                        <xsl:value-of select="." /><br/>
                                    </xsl:for-each>
                                </td>
			        		</tr>
		        		</xsl:for-each>
		        		</tbody>
		        	</table>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__MEMORY'">
                    <table cellspacing="0" class="listing listing content_table">
	        			<thead>
		        			<th>{*LC__CMDB__CATG__MODEL_TITLE*}</th>
		        			<th>{*LC__CATG__STORAGE_MANUFACTURER*}</th>
		        			<th>{*LC__CATD__SANPOOL_CAPACITY*}</th>
                            <th>{*LC__CATG__CONTACT_LIST__TYPE*}</th>
	        			</thead>
	        			<tbody>
		        		<xsl:for-each select="cat_data">
			        		<tr>
			        			<td><xsl:value-of select="title"/></td>
                                <td><xsl:value-of select="manufacturer"/></td>
                                <td><xsl:value-of select="capacity"/>&#xa0;<xsl:value-of select="unit"/></td>
                                <td><xsl:value-of select="type"/></td>
			        		</tr>
		        		</xsl:for-each>
		        		</tbody>
		        	</table>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__UNIVERSAL_INTERFACE'">
                    <table cellspacing="0" class="listing listing content_table">
	        			<thead>
		        			<th>{*LC__CMDB__CATG__MODEL_TITLE*}</th>
		        			<th>{*LC__CMDB__CATG__UI_CONNECTION_TYPE*}</th>
		        			<th>{*LC__CMDB__CATG__UI_PLUG_TYPE*}</th>
							<th>{*LC__CMDB__CATG__UI_ASSIGNED_UI*}</th>
	        			</thead>
	        			<tbody>
		        		<xsl:for-each select="cat_data">
			        		<tr>
			        			<td><xsl:value-of select="title"/></td>
								<td><xsl:value-of select="type"/></td>
								<td><xsl:value-of select="plug"/></td>
								<td>
                                    <xsl:choose>
                                        <xsl:when test="assigned_connector/sub_assigned_connector/@name != ''">
                                            <xsl:value-of select="assigned_connector/sub_assigned_connector"/> -> <xsl:value-of select="assigned_connector/sub_assigned_connector/@name"/>
                                        </xsl:when>
                                    </xsl:choose>
                                </td>
			        		</tr>
		        		</xsl:for-each>
		        		</tbody>
		        	</table>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__SOUND'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__COMPUTING_RESOURCES'">
                    <xsl:for-each select="cat_data">
                        <table cellspacing="0" class="keyvalue">
	        			    <tbody>
                                <tr>
                                    <td class="key">{*LC__CMDB__CATG__COMPUTING_RESOURCES__RAM*}:</td> <td class="value"><xsl:value-of select="ram" />&#xa0;<xsl:value-of select="ram_unit" /></td>
                                </tr>
                                <tr>
		        			        <td class="key">{*LC__CMDB__CATG__COMPUTING_RESOURCES__CPU*}:</td>   <td class="value"><xsl:value-of select="cpu" /> &#xa0;<xsl:value-of select="cpu_unit" /></td>
                                </tr>
                                <tr>
                                    <td class="key">{*LC__CMDB__CATG__COMPUTING_RESOURCES__DISC_SPACE*}:</td>   <td class="value"><xsl:value-of select="disc_space" /> &#xa0;<xsl:value-of select="disc_space_unit" /></td>
                                </tr>
                                <tr>
                                    <td class="key">{*LC__CMDB__CATG__COMPUTING_RESOURCES__NETWORK_BANDWIDTH*}:</td>  <td class="value"><xsl:value-of select="network_bandwidth" /> &#xa0;<xsl:value-of select="network_bandwidth_unit" /></td>
                                </tr>
                                 <tr>
                                    <td class="key">{*LC__LANGUAGEEDIT__TABLEHEADER_DESCRIPTION*}:</td> <td class="value"><xsl:value-of select="description" /></td>
                                 </tr>
	        			    </tbody>
		        	    </table>
                    </xsl:for-each>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__IMAGE'">
                    <xsl:for-each select="cat_data">
                        <table cellspacing="0" class="keyvalue">
	        			    <tbody>
                                <tr>
		        			        <td class="key">{*LC__CMDB__CATG__IMAGE_OBJ_FILE*}</td> <td class="value"><xsl:value-of select="image/@file_name" /></td>
                                </tr>
                                <tr>
		        			        <td class="key">{*LC__UNIVERSAL__VERSION_DESCRIPTION*}</td>   <td class="value"><xsl:value-of select="description" /></td>
                                </tr>
	        			    </tbody>
		        	    </table>
                    </xsl:for-each>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__FILE'">
                    <xsl:for-each select="cat_data">
                        <table cellspacing="0" class="keyvalue">
                        <tbody>
                            <tr>
                                <td class="key">{*LC__CMDB__CATG__IMAGE_OBJ_FILE*}</td> <td class="value"><xsl:value-of select="file_physical/@file_name" /></td>
                            </tr>
                            <tr>
                                <td class="key">{*LC__UNIVERSAL__VERSION_DESCRIPTION*}</td>   <td class="value"><xsl:value-of select="description" /></td>
                            </tr>
                        </tbody>
                        </table>
                    </xsl:for-each>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__VIRTUAL_DEVICE'">
                    <table cellspacing="0" class="listing listing content_table">
	        			<thead>
                            <th>{*LC__CMDB__CATG__VD__DEVICETYPE*}</th>
                            <th>{*LC__CMDB__CATG__VD__LOCAL_DEVICE*}</th>
                            <th>{*LC__CMDB__CATG__VD__HOST_RESOURCE*}</th>
		        			<th>{*LC__CMDB__CATG__VD__TYPE*}</th>
	        			</thead>
	        			<tbody>
		        		<xsl:for-each select="cat_data">
                                <xsl:choose>
                                    <xsl:when test="network_type/@id != 0">
                                <tr>
                                            <td><xsl:value-of select="device_type"/></td>
                                    <td><xsl:value-of select="local_port"/></td>
                                            <td><xsl:value-of select="host_port/@title"/></td>
                                    <td><xsl:value-of select="network_type"/></td>
                                </tr>
                                    </xsl:when>
                                    <xsl:when test="local_interface/@id != 0">
                                <tr>
                                            <td><xsl:value-of select="device_type"/></td>
                                            <td><xsl:value-of select="local_interface/@ref_title"/></td>
                                            <td><xsl:value-of select="host_interface"/> >> <xsl:value-of select="host_interface/@ref_title"/></td>
                                            <td><xsl:value-of select="local_interface/@ui_con_type"/></td>
                                </tr>
                                    </xsl:when>
                                    <xsl:when test="local_stor_device/@id != 0">
                                <tr>
                                            <td><xsl:value-of select="device_type"/></td>
                                            <td><xsl:value-of select="local_stor_device/@ref_title"/></td>
                                            <td>
                                                <xsl:choose>
                                                    <xsl:when test="host_ldev_client != ''">
                                                        <xsl:value-of select="host_ldev_client"/> >> <xsl:value-of select="host_ldev_client/@ref_title"/>
                                                    </xsl:when>
                                                    <xsl:when test="host_drive != ''">
                                                        <xsl:value-of select="host_drive"/> >> <xsl:value-of select="host_drive/@ref_title"/>
                                                    </xsl:when>
                                                    <xsl:when test="host_stor_device != ''">
                                                        <xsl:value-of select="host_stor_device"/> >> <xsl:value-of select="host_stor_device/@ref_title"/>
                                                    </xsl:when>
                                                    <xsl:when test="cluster_storage != ''">
                                                        <xsl:value-of select="cluster_storage"/> >> <xsl:value-of select="cluster_storage/@ref_title"/>
                                                    </xsl:when>
                                                </xsl:choose>
                                            </td>
                                    <td><xsl:value-of select="storage_type"/></td>
                                    <td></td>
                                </tr>
                                    </xsl:when>
                                </xsl:choose>
		        		</xsl:for-each>
		        		</tbody>
		        	</table>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__VIRTUAL_SWITCH'">
                    <table cellspacing="0" class="listing listing content_table">
	        			<thead>
		        			<th>{*LC__CONTACT__ORGANISATION_TITLE*}</th>
		        			<th>{*LC__CMDB__CATG__VIRTUAL_SWITCH__PORTS*}</th>
							<th>{*LC__CMDB__CATG__VSWITCH__PORT_GROUPS*}</th>
							<th>{*LC__CMDB__CATG__VSWITCH__SERVICE_CONSOLE_PORTS*}</th>
							<th>{*LC__CMDB__CATG__VSWITCH__VMKERNEL_PORTS*}</th>
	        			</thead>
	        			<tbody>
		        		<xsl:for-each select="cat_data">
			        		<tr>
			        			<td><xsl:value-of select="title"/></td>
								<td>
									<ul>
                                        <xsl:for-each select="ports/sub_ports">
                                            <li><xsl:value-of select="." /></li>
                                        </xsl:for-each>
									</ul>
								</td>
								<td>
									<ul>
                                        <xsl:for-each select="portgroup/sub_portgroup">
                                            <li><xsl:value-of select="." />
                                            <xsl:if test="./@vlanid">
                                            ({*LC__CMDB__CATG__VSWITCH__VLAN_ID*}: <xsl:value-of select="./@vlanid" />)
                                            </xsl:if>
                                            </li>
                                        </xsl:for-each>
									</ul>
								</td>
								<td>
									<ul>
                                        <xsl:for-each select="serviceconsoleports/sub_serviceconsoleports">
                                            <li><xsl:value-of select="." />
                                                    <xsl:if test="./@ip">
                                                     ({*LC__CATG__IP_ADDRESS*}: <xsl:value-of select="./@ip" />)
                                                    </xsl:if>
                                            </li>
                                        </xsl:for-each>
									</ul>
								</td>
								<td>
									<ul>
                                        <xsl:for-each select="vmkernelports/sub_vmkernelports">
                                            <li><xsl:value-of select="." />
                                            <xsl:if test="./@ip">
                                                     ({*LC__CATG__IP_ADDRESS*}: <xsl:value-of select="./@ip" />)
                                                    </xsl:if>
                                             </li>
                                        </xsl:for-each>
									</ul>
								</td>
			        		</tr>
		        		</xsl:for-each>
		        		</tbody>
		        	</table>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__POWER_CONSUMER'">
                    <table cellspacing="0" class="listing listing content_table">
	        			<thead>
		        			<th>{*LC__CMDB__CATG__POWER_CONSUMER__TITLE*}</th>
		        			<th>{*LC__CMDB__CATG__POWER_CONSUMER__MANUFACTURE*}</th>
		        			<th>{*LC__CMDB__CATG__POWER_CONSUMER__MODEL*}</th>
                            <th>{*LC__CMDB__CATG__POWER_SUPPLIER__VOLT*}</th>
                            <th>{*LC__CMDB__CATG__POWER_SUPPLIER__WATT*}</th>
                            <th>{*LC__CMDB__CATG__POWER_SUPPLIER__AMPERE*}</th>
                            <th>BTU</th>
                            <th>{*LC__CMDB__CATG__POWER_CONSUMER_CONNECTION*}</th>
	        			</thead>
	        			<tbody>
		        		<xsl:for-each select="cat_data">
			        		<tr>
			        			<td><xsl:value-of select="title"/></td>
                                <td><xsl:value-of select="manufacturer"/></td>
                                <td><xsl:value-of select="model"/></td>
                                <td><xsl:value-of select="volt"/></td>
                                <td><xsl:value-of select="watt"/></td>
                                <td><xsl:value-of select="ampere"/></td>
                                <td><xsl:value-of select="btu"/></td>
                                <td>
                                    <xsl:choose>
                                        <xsl:when test="assigned_connector/sub_assigned_connector/@name != ''">
                                            <xsl:value-of select="assigned_connector/sub_assigned_connector"/> -> <xsl:value-of select="assigned_connector/sub_assigned_connector/@name"/>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            -
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </td>
			        		</tr>
		        		</xsl:for-each>
		        		</tbody>
		        	</table>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__RAID'">
                    <table cellspacing="0" class="listing listing content_table">
	        			<thead>
		        			<th>{*LC__CMDB__CATG__TITLE*}</th>
		        			<th>{*LC__CATG__STORAGE_RAIDLEVEL*}</th>
							<th>{*LC__CMDB__RAID_TYPE*}</th>
		        			<th>{*LC__CATG__STORAGE_CONTROLLER*}</th>
							<th>{*LC__CMDB__CATG__STORAGE__CONNECTED_DEVICES*}</th>
							<th>{*LC__CATG__CMDB_MEMORY_TOTALCAPACITY*}</th>
	        			</thead>
	        			<tbody>
		        		<xsl:for-each select="cat_data">
			        		<tr>
			        			<td><xsl:value-of select="title"/></td>
                                <td><xsl:value-of select="raid_level"/></td>
			        			<td><xsl:value-of select="raid_type"/></td>
			        			<td><xsl:value-of select="controller"/></td>
                                <td>
                                    <xsl:for-each select="storages/sub_storages">
                                        <xsl:value-of select="." />&#xa0;(<xsl:value-of select="./@capacity" /> GB)<br/>
                                    </xsl:for-each>
                                </td>
                                <td><xsl:value-of select="full_capacity" /> GB</td>
			        		</tr>
		        		</xsl:for-each>
		        		</tbody>
		        	</table>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__RELATION'">
	        		<table cellspacing="0" class="listing listing content_table">
	        			<thead>
		        			<th>{*LC__CMDB__LOGBOOK__SOURCE__OBJECT*} 1</th>
		        			<th>{*LC__CMDB__LOGBOOK__SOURCE__OBJECT*} 2</th>
		        			<th>{*LC__CATG__RELATION__RELATION_TYPE*}</th>
		        			<th>{*LC__CATG__RELATION__WEIGHTING*}</th>
	        			</thead>
	        			<tbody>
		        		<xsl:for-each select="cat_data">
			        		<tr>
			        			<td><xsl:value-of select="object1"/></td>
			        			<td><xsl:value-of select="object2"/></td>
			        			<td><xsl:value-of select="relation_type"/></td>
			        			<td><xsl:value-of select="weighting"/></td>
			        		</tr>
		        		</xsl:for-each>
		        		</tbody>
		        	</table>
	        	</xsl:when>

                <xsl:when test="@const = 'C__CATG__EMERGENCY_PLAN'">
	        		<table cellspacing="0" class="listing listing content_table">
	        			<thead>
		        			<th>{*LC__CMDB__CATG__EMERGENCY_PLAN_TITLE*}</th>
		        			<th>{*LC__CMDB__OBJTYPE*}</th>
		        			<th>{*LC__CMDB__CATG__GLOBAL_TITLE*}</th>
                            <th>{*LC__CMDB__CATS__EMERGENCY_PLAN_CALC_TIME_NEEDED*}</th>
                            <th>{*LC__CMDB__CATS__EMERGENCY_PLAN_PRACTICE_ACTUAL_DATE*}</th>
	        			</thead>
	        			<tbody>
		        		<xsl:for-each select="cat_data">
			        		<tr>
			        			<td><xsl:value-of select="title"/></td>
			        			<td><xsl:value-of select="emergency_plan/@type_title"/></td>
			        			<td><xsl:value-of select="emergency_plan"/></td>
                                <td><xsl:value-of select="time_needed"/></td>
                                <td><xsl:value-of select="practice_date"/></td>
			        		</tr>
		        		</xsl:for-each>
		        		</tbody>
		        	</table>
	        	</xsl:when>

                <xsl:when test="@const = 'C__CATG__LDEV_CLIENT'">
	        		<table cellspacing="0" class="listing listing content_table">
	        			<thead>
		        			<th>{*LC__CMDB__CATS__NET__TITLE*}</th>
		        			<th>{*LC__CMDB__FC_PATH*}</th>
                            <th>{*LC__CMDB__CATG__LDEV_SERVER*}</th>
                            <th>{*LC__CMDB__CATG__LDEV_MULTI_PATH*}</th>
                        </thead>
	        			<tbody>
		        		<xsl:for-each select="cat_data">
			        		<tr>
			        			<td><xsl:value-of select="title"/></td>
			        			<td>
								<xsl:for-each select="paths/sub_paths" >
									<xsl:value-of select="." /><br/>
								</xsl:for-each >
                                </td>
			        			<td><xsl:value-of select="assigned_ldevserver"/></td>
                                <td><xsl:value-of select="multipath"/></td>

                            </tr>
		        		</xsl:for-each>
		        		</tbody>
		        	</table>
	        	</xsl:when>

                <xsl:when test="@const = 'C__CATG__IT_SERVICE'">
	        		<table cellspacing="0" class="listing listing content_table">
	        			<thead>
		        			<th>{*LC__CMDB__CATS__FILE_VERSION_TITLE*}</th>
		        			<th>{*LC__CMDB__CATS__NET__TYPE*}</th>
	        			</thead>
	        			<tbody>
		        		<xsl:for-each select="cat_data">
			        		<tr>
			        			<td><xsl:value-of select="connected_object"/></td>
			        			<td><xsl:value-of select="connected_object/@type_title"/></td>
			        		</tr>
		        		</xsl:for-each>
		        		</tbody>
		        	</table>
	        	</xsl:when>
                <xsl:when test="@const = 'C__CATG__GRAPHIC'">
                    <table cellspacing="0" class="listing listing content_table">
	        			<thead>
		        			<th>{*LC__CATG__RAID_TITLE*}</th>
		        			<th>{*LC__CMDB__CATG__MANUFACTURE*}</th>
		        			<th>{*LC__CMDB__CATG__MEMORY*}</th>
	        			</thead>
	        			<tbody>
		        		<xsl:for-each select="cat_data">
			        		<tr>
			        			<td><xsl:value-of select="title"/></td>
			        			<td><xsl:value-of select="manufacturer"/></td>
			        			<td><xsl:value-of select="memory"/>  <xsl:value-of select="unit"/> </td>
			        		</tr>
		        		</xsl:for-each>
		        		</tbody>
		        	</table>
	        	</xsl:when>

                <xsl:when test="@const = 'C__CATG__CONNECTOR'">
	        		<table cellspacing="0" class="listing listing content_table">
	        			<thead>
							<th>{*LC__UNIVERSAL__TITLE*}</th>
							<th>{*LC__CATG__CONNECTOR__INOUT*}</th>
							<th>{*LC__CATG__CONNECTOR__CATEGORY_TYPE*}</th>
							<th>{*LC__CATG__CONNECTOR__SIBLING_IN_OR_OUT*}</th>
							<th>{*LC__CMDB__OBJTYPE__WIRING_SYSTEM*}</th>
		        			<th>{*LC__CATG__CONNECTOR__CONNECTION_TYPE*}</th>
		        			<th>{*LC__CMDB__CATG__UI_ASSIGNED_UI*}</th>
	        			</thead>
	        			<tbody>
		        		<xsl:for-each select="cat_data">
			        		<tr>
			        			<td><xsl:value-of select="title"/></td>
			        			<td>
									<xsl:choose>
										<xsl:when test="type/@id = '1'">
											{*LC__CATG__CONNECTOR__INPUT*}
										</xsl:when>
										<xsl:when test="type/@id = '2'">
											{*LC__CATG__CONNECTOR__OUTPUT*}
										</xsl:when>
									</xsl:choose>
								</td>
								<td><xsl:value-of select="assigned_category"/></td>
								<td><xsl:value-of select="connector_sibling"/></td>
								<td><xsl:value-of select="wiring_system"/></td>
								<td><xsl:value-of select="connection_type"/></td>
			        			<td>
									<xsl:value-of select="assigned_connector/sub_assigned_connector"/> &gt; <xsl:value-of select="assigned_connector/sub_assigned_connector/@name"/>
								</td>

			        		</tr>
		        		</xsl:for-each>
		        		</tbody>
		        	</table>
	        	</xsl:when>

                 <xsl:when test="@const = 'C__CATG__SOA_STACKS'">
	        		<table cellspacing="0" class="listing listing content_table">
	        			<thead>
		        			<th>{*LC__CMDB__LOGBOOK__TITLE*}</th>
		        			<th>{*LC__CMDB__CATG__SOA_COMPONENTS*}</th>
		        			<th>{*LC__CMDB__CATG__IT_SERVICE*}</th>
	        			</thead>
	        			<tbody>
		        		<xsl:for-each select="cat_data">
			        		<tr>
			        			<td><xsl:value-of select="title"/></td>
								<td>
									<ul>
										<xsl:for-each select="soa_stack_components/sub_soa_stack_components">
											<li><xsl:value-of select="." /></li>
										</xsl:for-each>
									</ul>
								</td>
								<td>
									<ul>
										<xsl:for-each select="soa_stack_it_services/sub_soa_stack_it_services">
											<li><xsl:value-of select="." /></li>
										</xsl:for-each>
									</ul>
								</td>
			        		</tr>
		        		</xsl:for-each>
		        		</tbody>
		        	</table>
	        	</xsl:when>

				<xsl:when test="@const = 'C__CATG__MAINTENANCE'">
	        		<table cellspacing="0" class="listing listing content_table">
	        			<thead>
		        			<th>{*LC__CMDB__CATG__TITLE*}</th>
		        			<th>{*LC__CMDB__CATG__MAINTENANCE_OBJ_MAINTENANCE*}</th>
		        			<th>{*LC__CMDB__CATS__MAINTENANCE_CONTRACT_DURATION_START*}</th>
		        			<th>{*LC__CMDB__CATS__MAINTENANCE_CONTRACT_DURATION_END*}</th>
							<th>{*LC__CMDB__CATS__MAINTENANCE_CONTRACT_REACTION_RATE*}</th>
							<th>{*LC__CMDB__CATS__MAINTENANCE_CONTRACT_TYPE*}</th>
							<th>{*LC__UNIVERSAL__STATUS*}</th>
							<th>{*LC__CMDB__CATS__MAINTENANCE_PRODUCT*}</th>
							<th>{*LC__CMDB__CATS__MAINTENANCE_CONTRACT_DISTRIBUTOR*}</th>
							<th>{*LC__CMDB__CATS__MAINTENANCE_TERMINATED_ON*}</th>
	        			</thead>
	        			<tbody>
		        		<xsl:for-each select="cat_data">
			        		<tr>
			        			<td><xsl:value-of select="title"/></td>
			        			<td><xsl:value-of select="maintenance"/></td>
			        			<td><xsl:value-of select="start"/></td>
			        			<td><xsl:value-of select="end"/></td>
								<td><xsl:value-of select="reaction_rate"/></td>
								<td><xsl:value-of select="contract_type"/></td>
								<td><xsl:value-of select="status"/></td>
								<td><xsl:value-of select="product"/></td>
								<td><xsl:value-of select="contact"/></td>
								<td><xsl:value-of select="terminated"/></td>
			        		</tr>
		        		</xsl:for-each>
		        		</tbody>
		        	</table>
	        	</xsl:when>

				<xsl:when test="@const = 'C__CATS__SAN_ZONING'">
	        		<table cellspacing="0" class="keyvalue" >
						<tr>
							<td class="key">
								{*LC__CONTACT__TREE__MEMBERS*}:
							</td>
							<td class="value">
								<xsl:for-each select="cat_data/members/paths">
									<xsl:value-of select="."/><br/>
								</xsl:for-each>
							</td>
						</tr>
						<tr>
							<td class="key">
								{*LC__CMDB__CAT__COMMENTARY*}:
							</td>
							<td class="value">
								<xsl:value-of select="cat_data/description"/>
							</td>
						</tr>
		        	</table>
	        	</xsl:when>

				<xsl:when test="@const = 'C__CMDB__SUBCAT__FILE_OBJECTS'">
	        		<table cellspacing="0" class="listing listing content_table" >
						<thead>
		        			<th>{*LC_UNIVERSAL__OBJECT*}</th>
		        			<th>{*LC__CMDB__OBJTYPE*}</th>
							<th>{*LC__CMDB__CATG__GLOBAL_CATEGORY*}</th>
	        			</thead>
						<tbody>
						<xsl:for-each select="cat_data/file_objects/item">
							<tr>
								<td >
									<xsl:value-of select="."/>
								</td>
								<td>
									<xsl:value-of select="@object_type"/>
								</td>
								<td>
									<xsl:choose>
										<xsl:when test="@const = 'C__CATG__FILE'">
											{*LC__CMDB__CATG__FILE*}
										</xsl:when>
										<xsl:when test="@const = 'C__CATG__MANUAL'">
											{*LC__CMDB__CATG__MANUAL*}
										</xsl:when>
										<xsl:when test="@const = 'C__CATG__EMERGENCY_PLAN'">
											{*LC__CATG_EMERGENCY_PLAN*}
										</xsl:when>
									</xsl:choose>
								</td>
							</tr>
						</xsl:for-each>
						</tbody>
		        	</table>
	        	</xsl:when>

				<xsl:when test="@const = 'C__CATS__FILE_OBJECTS'">
	        		<table cellspacing="0" class="listing listing content_table" >
						<thead>
		        			<th>{*LC_UNIVERSAL__OBJECT*}</th>
		        			<th>{*LC__CMDB__OBJTYPE*}</th>
							<th>{*LC__CMDB__CATG__GLOBAL_CATEGORY*}</th>
	        			</thead>
						<tbody>
						<xsl:for-each select="cat_data/file_objects/item">
							<tr>
								<td >
									<xsl:value-of select="."/>
								</td>
								<td>
									<xsl:value-of select="@object_type"/>
								</td>
								<td>
									<xsl:choose>
										<xsl:when test="@const = 'C__CATG__FILE'">
											{*LC__CMDB__CATG__FILE*}
										</xsl:when>
										<xsl:when test="@const = 'C__CATG__MANUAL'">
											{*LC__CMDB__CATG__MANUAL*}
										</xsl:when>
										<xsl:when test="@const = 'C__CATG__EMERGENCY_PLAN'">
											{*LC__CATG_EMERGENCY_PLAN*}
										</xsl:when>
									</xsl:choose>
								</td>
							</tr>
						</xsl:for-each>
						</tbody>
		        	</table>
	        	</xsl:when>

				<xsl:when test="@const = 'C__CATS__NET'">

	        		<table cellspacing="0" class="listing" >
						<xsl:for-each select="cat_data">
						<tr>
							<td class="key">
								{*LC__CMDB__CATG__TYPE*}:
							</td>
							<td class="value">
								<xsl:value-of select="type/."/>
							</td>
						</tr>
						<tr>
							<td class="key">
								{*LC__CMDB__CATS__NET*}:
							</td>
							<td class="value">
								<xsl:value-of select="address/."/> / <xsl:value-of select="cidr_suffix/."/>
							</td>
						</tr>
						<tr>
							<td class="key">
								{*LC__CMDB__CATS__NET__MASK*}:
							</td>
							<td class="value">
								<xsl:value-of select="netmask/."/>
							</td>
						</tr>
                        <tr>
                            <td class="key">
                                {*LC__CMDB__CATS__NET__DHCP_RANGE*}:
                            </td>
                            <td class="value">
                                <xsl:value-of select="range_from/."/> - <xsl:value-of select="range_to/."/>
                            </td>
                        </tr>
						<tr>
							<td class="key">
								{*LC__CATP__IP__DEFAULTGATEWAY*}:
							</td>
							<td class="value">
								<xsl:value-of select="gateway/."/> >> <xsl:value-of select="gateway/@ref_title"/>
							</td>
						</tr>
                        <tr>
                            <td class="key">
                                {*LC__CATS__NET__REVERSE_DNS*}:
                            </td>
                            <td class="value">
                                <xsl:value-of select="reverse_dns/."/>
                            </td>
                        </tr>
						<tr>
							<td class="key">
								{*LC__CMDB__CATS__NET__DNS_SERVER*}:
							</td>
							<td class="value">
								<xsl:for-each select="dns_server/sub_dns_server">
									<xsl:value-of select="@title"/> >> <xsl:value-of select="@ref_title"/><br/>
								</xsl:for-each>
							</td>
						</tr>
						<tr>
							<td class="key">
								{*LC__CMDB__CATS__NET__DNS_DOMAIN*}:
							</td>
							<td class="value">
								<xsl:for-each select="dns_domain/sub_dns_domain">
									<xsl:value-of select="@title"/><br/>
								</xsl:for-each>
							</td>
						</tr>
                        <tr>
                            <td class="key">
                                {*LC__CMDB__CATS__NET__LAYER2_NET*}:
                            </td>
                            <td class="value">
                                <xsl:for-each select="layer2_assignments/sub_layer2_assignments">
                                    <xsl:value-of select="@title"/><br/>
                                </xsl:for-each>
                            </td>
                        </tr>
						<tr>
							<td class="key">
								{*LC__CMDB__CAT__COMMENTARY*}:
							</td>
							<td class="value">
								<xsl:value-of select="description/."/>
							</td>
						</tr>
						</xsl:for-each>
		        	</table>

	        	</xsl:when>

                <xsl:when test="@const = 'C__CATG__ASSIGNED_LOGICAL_UNIT'">
                    <table cellspacing="0" class="listing content_table">
                            <thead>
                                <th>{*LC__UNIVERSAL__ID*}</th>
                                <th>{*LC__CMDB__CATG__ASSIGNED_LOGICAL_UNITS*}</th>
                                <th>{*LC_UNIVERSAL__OBJECT_TYPE*}</th>
                            </thead>
                            <tbody>
                            <xsl:for-each select="cat_data">
                                <tr class="">
                                    <td class="value">
                                        <xsl:value-of select="assigned_object/@id"/>
                                    </td>
                                    <td class="value">
                                        <xsl:value-of select="assigned_object/."/>
                                    </td>
                                    <td class="value">
                                        <xsl:value-of select="assigned_object/@type_title"/>
                                    </td>
                                </tr>
                            </xsl:for-each>
                            </tbody>
					</table>
	        	</xsl:when>

                <xsl:when test="@const = 'C__CATG__GUEST_SYSTEMS'">
                    <table cellspacing="0" class="listing content_table">
                        <thead>
                            <th>{*LC__CMDB__CATG__GUEST_SYSTEMS*}</th>
                            <th>{*LC_UNIVERSAL__OBJECT_TYPE*}</th>
                            <th>{*LC__CATP__IP__HOSTNAME*}</th>
                            <th>{*LC__CMDB__CATG__NETWORK__PRIM_IP*}</th>
                            <th>{*LC__CMDB__CATG__GUEST_SYSTEM_RUNS_ON*}</th>
                        </thead>
                        <tbody>
                            <xsl:for-each select="cat_data">
                                <tr>
                                    <td>
                                        <xsl:value-of select="connected_object"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="connected_object/@type_title"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="hostname"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="hostname/@ip"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="runs_on"/>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__SLA'">

                    <table cellspacing="0" class="listing" >
                        <xsl:for-each select="cat_data">
                            <tr>
                                <td class="key">
                                    {*LC__CMDB__CATG__SLA_SERVICE_ID*}:
                                </td>
                                <td class="value">
                                    <xsl:value-of select="service_id/."/>
                                </td>
                            </tr>
                            <tr>
                                <td class="key">
                                    {*LC__CMDB__CATG__SLA_SERVICELEVEL*}:
                                </td>
                                <td class="value">
                                    <xsl:value-of select="service_level/."/>&#160;<xsl:value-of select="service_level_dialog/."/>
                                </td>
                            </tr>
                            <tr>
                                <td class="key">
                                    {*LC__CMDB__CATS__SLA_SCHEMA__SERVICETIMEFRAME*}:
                                </td>
                                <td class="value">
                                    <ul>
                                        <li><xsl:value-of select="monday_time/@title" />&#160;<xsl:value-of select="monday_time" /></li>
                                        <li><xsl:value-of select="tuesday_time/@title" />&#160;<xsl:value-of select="tuesday_time" /></li>
                                        <li><xsl:value-of select="wednesday_time/@title" />&#160;<xsl:value-of select="wednesday_time" /></li>
                                        <li><xsl:value-of select="thursday_time/@title" />&#160;<xsl:value-of select="thursday_time" /></li>
                                        <li><xsl:value-of select="friday_time/@title" />&#160;<xsl:value-of select="friday_time" /></li>
                                        <li><xsl:value-of select="saturday_time/@title" />&#160;<xsl:value-of select="saturday_time" /></li>
                                        <li><xsl:value-of select="sunday_time/@title" />&#160;<xsl:value-of select="sunday_time" /></li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <td class="key">
                                    {*LC__CMDB__CATS__SLA_SCHEMA__SERVICETIMEFRAME*}:
                                </td>
                                <td class="value">
                                    <xsl:value-of select="reaction_time" />&#160;<xsl:value-of select="reaction_time_unit" />
                                </td>
                            </tr>
                            <tr>
                                <td class="key">
                                    {*LC__CMDB__CATS__SLA_RECOVERYTIME*}:
                                </td>
                                <td class="value">
                                    <xsl:value-of select="recovery_time" />&#160;<xsl:value-of select="recovery_time_unit" />
                                </td>
                            </tr>
                            <tr>
                                <td class="key">{*LC__CMDB__LOGBOOK__DESCRIPTION*}:</td>
                                <td class="value">
                                    <xsl:value-of select="description" />
                                </td>
                            </tr>
                        </xsl:for-each>
                    </table>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__ASSIGNED_CARDS'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__ASSIGNED_CARDS'">
                    <table cellspacing="0" class="listing content_table">
                        <thead>
                            <th>{*LC__CMDB__CATG__UI_TITLE*}</th>
                            <th>{*LC__CMDB__CATG__TYPE*}</th>
                        </thead>
                        <tbody>
                            <xsl:for-each select="cat_data">
                                <tr>
                                    <td>
                                        <xsl:value-of select="connected_obj"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="connected_obj/@type_title"/>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__OBJECT'">
                    <table cellspacing="0" class="listing content_table">
                        <thead>
                            <th>{*LC__UNIVERSAL__ID*}</th>
                            <th>{*LC__CMDB__CATG__UI_TITLE*}</th>
                            <th>{*LC__CMDB__CATG__TYPE*}</th>
                        </thead>
                        <tbody>
                            <xsl:for-each select="cat_data">
                                <tr>
                                    <td>
                                        <xsl:value-of select="assigned_object/@id"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="assigned_object"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="assigned_object/@type_title"/>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__EMERGENCY_PLAN_LINKED_OBJECTS'">
                    <table cellspacing="0" class="listing content_table">
                        <thead>
                            <th>{*LC__UNIVERSAL__ID*}</th>
                            <th>{*LC__CMDB__CATG__UI_TITLE*}</th>
                            <th>{*LC__CMDB__CATG__TYPE*}</th>
                        </thead>
                        <tbody>
                            <xsl:for-each select="cat_data">
                                <tr>
                                    <td>
                                        <xsl:value-of select="object/@id"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="object"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="object/@type_title"/>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </xsl:when>

                <xsl:when test="@const = 'C__CMDB__SUBCAT__EMERGENCY_PLAN_LINKED_OBJECT_LIST'">
                    <table cellspacing="0" class="listing content_table">
                        <thead>
                            <th>{*LC__UNIVERSAL__ID*}</th>
                            <th>{*LC__CMDB__CATG__UI_TITLE*}</th>
                            <th>{*LC__CMDB__CATG__TYPE*}</th>
                        </thead>
                        <tbody>
                            <xsl:for-each select="cat_data">
                                <tr>
                                    <td>
                                        <xsl:value-of select="object/@id"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="object"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="object/@type_title"/>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__NET_IP_ADDRESSES'">
                    <table cellspacing="0" class="listing content_table">
                        <thead>
                            <th>{*LC__CATP__IP__ASSIGN*}</th>
                            <th>{*LC__CATG__IP_ADDRESS*}</th>
                            <th>{*LC_UNIVERSAL__OBJECT*}</th>
                        </thead>
                        <tbody>
                            <xsl:for-each select="cat_data">
                                <tr>
                                    <td>
                                        <xsl:choose>
                                            <xsl:when test="net_type/@id = 1">
                                                <xsl:value-of select="ipv4_assignment"></xsl:value-of>
                                            </xsl:when>
                                            <xsl:when test="net_type/@id = 1000">
                                                <xsl:value-of select="ipv6_assignment"></xsl:value-of>
                                            </xsl:when>
                                        </xsl:choose>
                                    </td>
                                    <td>
                                        <xsl:value-of select="title"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="object"/>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__UPS'">
                    <table class="keyvalue" cellspacing="0">
                        <tr>
                            <td class="key"><xsl:value-of select="cat_data/type/@title"/>:</td>
                            <td class="value" style="font-weight:bold"><xsl:value-of select="cat_data/type"/></td>
                        </tr>
                        <tr>
                            <td class="key"><xsl:value-of select="cat_data/battery_type/@title"/>:</td>
                            <td class="value" style="font-weight:bold"><xsl:value-of select="cat_data/battery_type"/></td>
                        </tr>
                        <tr>
                            <td class="key"><xsl:value-of select="cat_data/amount/@title"/>:</td>
                            <td class="value" style="font-weight:bold"><xsl:value-of select="cat_data/amount"/></td>
                        </tr>
                        <tr>
                            <td class="key"><xsl:value-of select="cat_data/charge_time/@title"/>:</td>
                            <td class="value" style="font-weight:bold"><xsl:value-of select="cat_data/charge_time"/>&#160;<xsl:value-of select="cat_data/charge_time_unit"/></td>
                        </tr>
                        <tr>
                            <td class="key"><xsl:value-of select="cat_data/autonomy_time/@title"/>:</td>
                            <td class="value" style="font-weight:bold"><xsl:value-of select="cat_data/autonomy_time"/>&#160;<xsl:value-of select="cat_data/autonomy_time_unit"/></td>
                        </tr>
                        <tr>
                            <td class="key" valign="top"><xsl:value-of select="cat_data/description/@title"/>:</td>
                            <td class="value">
                                <xsl:call-template name="nl2br">
                                    <xsl:with-param name="text" select="cat_data/description" />
                                </xsl:call-template>
                            </td>
                        </tr>
                    </table>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__ORGANIZATION_PERSONS'">
                    <table cellspacing="0" class="listing content_table">
                        <thead>
                            <th>{*LC__CONTACT__PERSON_FIRST_NAME*}</th>
                            <th>{*LC__CONTACT__PERSON_LAST_NAME*}</th>
                            <th>{*LC__CONTACT__PERSON_DEPARTMENT*}</th>
                            <th>{*LC__CONTACT__PERSON_TELEPHONE_COMPANY*}</th>
                            <th>{*LC__CONTACT__PERSON_MAIL_ADDRESS*}</th>
                            <th>{*LC__CONTACT__PERSON_ASSIGNED_ORGANISATION*}</th>
                        </thead>
                        <tbody>
                            <xsl:for-each select="cat_data">
                                <tr>
                                    <td>
                                        <xsl:value-of select="contact/sub_contact/@first_name"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="contact/sub_contact/@last_name"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="contact/sub_contact/@department"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="contact/sub_contact/@phone_company"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="contact/sub_contact/@mail"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="contact/sub_contact/@company_title"/>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__PERSON_ASSIGNED_GROUPS'">
                    <table cellspacing="0" class="listing content_table">
                        <thead>
                            <th>{*LC_UNIVERSAL__OBJECT*}</th>
                            <th>{*LC__CONTACT__GROUP_EMAIL_ADDRESS*}</th>
                            <th>{*LC__CONTACT__GROUP_PHONE*}</th>
                            <th>{*LC__CONTACT__GROUP_LDAP_GROUP*}</th>
                        </thead>
                        <tbody>
                            <xsl:for-each select="cat_data">
                                <tr>
                                    <td>
                                        <xsl:value-of select="connected_object"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="contact/sub_contact/@email_address"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="contact/sub_contact/@phone"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="contact/sub_contact/@ldap_group"/>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__PERSON_MASTER' or @const = 'C__CATS__PERSON'">
                    <ul>
                        <xsl:choose>
                            <xsl:when test="cat_data/ldap_id/@id > 0">
                                <li>
                                    <fieldset>
                                        <legend><b>LDAP Info</b></legend>
                                    <table class="keyvalue">
                                        <tr>
                                            <td class="key">
                                                {*LC__CMDB__TREE__SYSTEM__INTERFACE__LDAP__SERVER*}:
                                            </td>
                                            <td class="value">
                                                <xsl:value-of select="cat_data/ldap_id/."></xsl:value-of>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="key">
                                                DN:
                                            </td>
                                            <td class="value">
                                                <xsl:value-of select="cat_data/ldap_dn/."></xsl:value-of>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="key">
                                                CN:
                                            </td>
                                            <td class="value">
                                                <xsl:choose>
                                                    <xsl:when test="contains(cat_data/ldap_dn/., 'CN=')">
                                                        <xsl:value-of select="substring-after(substring-before(cat_data/ldap_dn/., ','), 'CN=')" disable-output-escaping="yes"/>
                                                    </xsl:when>
                                                </xsl:choose>
                                            </td>
                                        </tr>
                                    </table>
                                    </fieldset>
                                </li>
                            </xsl:when>
                        </xsl:choose>
                        <li>
                            <table class="keyvalue">
                            <xsl:for-each select="cat_data/*">
                                <xsl:choose>
                                    <xsl:when test="local-name() != 'ldap_dn' and local-name() != 'ldap_id'">
                                        <tr>
                                            <td class="key" style="vertical-align:top">
                                                <xsl:choose>
                                                    <xsl:when test="@title">
                                                        <xsl:value-of select="@title"/>:
                                                    </xsl:when>
                                                    <xsl:otherwise>
                                                        <xsl:value-of select="local-name()"/>:
                                                    </xsl:otherwise>
                                                </xsl:choose>
                                            </td>
                                            <td class="value">
                                                <xsl:choose>
                                                    <xsl:when test="local-name() = 'description'">
                                                        <xsl:call-template name="nl2br">
                                                            <xsl:with-param name="text" select="." />
                                                        </xsl:call-template>
                                                    </xsl:when>
                                                    <xsl:otherwise>
                                                        <xsl:value-of select="."/>
                                                    </xsl:otherwise>
                                                </xsl:choose>
                                            </td>
                                        </tr>
                                    </xsl:when>
                                </xsl:choose>
                            </xsl:for-each>
                            </table>
                        </li>
                    </ul>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__PERSON_GROUP_MEMBERS'">
                    <table cellspacing="0" class="listing content_table">
                        <thead>
                            <th>{*LC__CONTACT__PERSON_FIRST_NAME*}</th>
                            <th>{*LC__CONTACT__PERSON_LAST_NAME*}</th>
                            <th>{*LC__CONTACT__PERSON_DEPARTMENT*}</th>
                            <th>{*LC__CONTACT__PERSON_TELEPHONE_COMPANY*}</th>
                            <th>{*LC__CONTACT__PERSON_MAIL_ADDRESS*}</th>
                            <th>{*LC__CONTACT__PERSON_ASSIGNED_ORGANISATION*}</th>
                            <th>{*LC__CONTACT__PERSON_USER_NAME*}</th>
                        </thead>
                        <tbody>
                            <xsl:for-each select="cat_data">
                                <tr>
                                    <td>
                                        <xsl:value-of select="first_name"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="last_name"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="department"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="phone_company"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="email_address"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="organization"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="title"/>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__DATABASE_ACCESS'">
                    <table cellspacing="0" class="listing content_table">
                        <thead>
                            <th>{*LC__CMDB__CATS__APPLICATION*} / {*LC__CMDB__CATS__SERVICE*}</th>
                        </thead>
                        <tbody>
                            <xsl:for-each select="cat_data">
                                <tr>
                                    <td>
                                        <xsl:value-of select="access/sub_access[1]"/> {*LC__RELATION_TYPE__MASTER__APPLICATION_RUNS_ON*} <xsl:value-of select="access/sub_access[2]"/>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__LAYER2_NET_ASSIGNED_PORTS'">
                    <table cellspacing="0" class="listing content_table">
                        <thead>
                            <th>{*LC__CMDB__CATG__PORT__MAC*}</th>
                            <th>{*LC_UNIVERSAL__OBJECT*}</th>
                        </thead>
                        <tbody>
                            <xsl:for-each select="cat_data">
                                <tr>
                                    <td>
                                        <xsl:value-of select="isys_catg_port_list__id/@mac" />
                                    </td>
                                    <td>
                                        <xsl:choose>
                                            <xsl:when test="count(isys_catg_port_list__id/*) > 0">
                                                <xsl:value-of select="isys_catg_port_list__id/@title" /> (<xsl:value-of select="isys_catg_port_list__id/@type_title" />)
                                            </xsl:when>
                                            <xsl:otherwise>
                                                <xsl:value-of select="isys_catg_port_list__id/." /> (<xsl:value-of select="isys_catg_port_list__id/@type_title" />)
                                            </xsl:otherwise>
                                        </xsl:choose>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__CONTRACT_ALLOCATION'">
                    <table cellspacing="0" class="listing content_table">
                        <thead>
                            <th>{*LC_UNIVERSAL__OBJECT_TYPE*}</th>
                            <th>{*LC__UNITS__TITLE*}</th>
                        </thead>
                        <tbody>
                            <xsl:for-each select="cat_data">
                                <tr>
                                    <td>
                                        <xsl:value-of select="assigned_object/@type_title" />
                                    </td>
                                    <td>
                                        <xsl:value-of select="assigned_object/." />
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__CHASSIS_DEVICES'">
                    <table cellspacing="0" class="listing content_table">
                        <thead>
                            <th>{*LC__UNIVERSAL__ASSIGNMENT*}</th>
                            <th>{*LC__CMDB__CATS__CHASSIS__ROLE*}</th>
                            <th>{*LC__CMDB__CATS__CHASSIS__ASSIGNED_SLOTS*}</th>
                        </thead>
                        <tbody>
                            <xsl:for-each select="cat_data">
                                <tr>
                                    <td>
                                        <xsl:choose>
                                            <xsl:when test="assigned_device/@id != 0">
                                                <xsl:value-of select="assigned_device/." />
                                            </xsl:when>
                                            <xsl:when test="assigned_hba/@id != 0">
                                                <xsl:value-of select="assigned_hba/@ref_title" />
                                            </xsl:when>
                                            <xsl:when test="assigned_interface/@id != 0">
                                                <xsl:value-of select="assigned_interface/@ref_title" />
                                            </xsl:when>
                                            <xsl:when test="assigned_power_cosumer/@id != 0">
                                                <xsl:value-of select="assigned_power_cosumer/@ref_title" />
                                            </xsl:when>
                                        </xsl:choose>
                                    </td>
                                    <td>
                                        <xsl:value-of select="role/." />
                                    </td>
                                    <td>
                                        <xsl:value-of select="assigned_slots/." />
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__PERSON_ASSIGNED_WORKSTATION'">
                    <table class="keyvalue">
                        <tr>
                            <td class="key">
                                {*LC__CMDB__CATG__PERSON_ASSIGNED_WORKSTATION*}:
                            </td>
                            <td class="value">
                                <ul>
                                <xsl:for-each select="cat_data">
                                    <li>- <xsl:value-of select="assigned_workstations/." /></li>
                                </xsl:for-each>
                                </ul>
                            </td>
                        </tr>
                    </table>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__CONTROLLER'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__BACKUP__ASSIGNED_OBJECTS'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__BACKUP'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__SHARES'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__NETWORK_INTERFACE'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CMDB__SUBCAT__NETWORK_INTERFACE_P'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__SOUND'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__CPU'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__PASSWD'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__HBA'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__IT_SERVICE_COMPONENTS'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__GROUP_MEMBERSHIPS'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__INVOICE'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__CONTRACT_ASSIGNMENT'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__DATABASE_ASSIGNMENT'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATG__STACKING'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__NET_DHCP'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__LICENCE_LIST'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CMDB__SUBCAT__LICENCE_LIST'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__GROUP'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__APPLICATION_ASSIGNED_OBJ'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__FILE_VERSIONS'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CMDB__SUBCAT__FILE_VERSIONS'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__WS_ASSIGNMENT'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CMDB__SUBCAT__WS_ASSIGNMENT'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__PERSON_CONTACT_ASSIGNMENT'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__CLUSTER_SERVICE'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__DATABASE_GATEWAY'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__DATABASE_LINKS'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__DATABASE_OBJECTS'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__REPLICATION_PARTNER'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:when test="@const = 'C__CATS__APPLICATION_VARIANT'">
                    <xsl:call-template name="cat_data_multi">
                    </xsl:call-template>
                </xsl:when>

                <xsl:otherwise>
		        	<xsl:choose>
		        		<xsl:when test="count(cat_data) > 1">
	        				<xsl:for-each select="cat_data">
	        					<fieldset style="margin-bottom:5px;">
	        						<table class="keyvalue">
	        							<xsl:for-each select="*">
	        								<tr>
												<td class="key">
													<xsl:choose>
														<xsl:when test="starts-with(local-name(), 'html_')">
														</xsl:when>
														<xsl:when test="@title!=''">
															<xsl:value-of select="@title"/>:
														</xsl:when>
														<xsl:otherwise>
															<xsl:value-of select="local-name()"/>:
														</xsl:otherwise>
													</xsl:choose>
												</td>
												<td class="value">
                                                    <xsl:choose>
                                                        <xsl:when test="count(current()/*) > 1">
                                                            <ul>
                                                            <xsl:for-each select="*">
                                                                <li>- <xsl:value-of select="."/></li>
                                                            </xsl:for-each>
                                                            </ul>
                                                        </xsl:when>
                                                        <xsl:otherwise>
                                                            <xsl:call-template name="nl2br">
                                                                <xsl:with-param name="text" select="." />
                                                            </xsl:call-template>
                                                        </xsl:otherwise>
                                                    </xsl:choose>
                                                </td>
											</tr>
	        							</xsl:for-each>
	        						</table>
	        					</fieldset>
	        				</xsl:for-each>
		        		</xsl:when>
                        <!-- This works only with simple xml schemas -->
                        <xsl:when test="@multivalued = 1">
                            <xsl:call-template name="cat_data_multi">
                            </xsl:call-template>
                        </xsl:when>
		        		<xsl:otherwise>
		        			<ul>
		        				<xsl:apply-templates select="cat_data"/>
		        			</ul>
		        		</xsl:otherwise>
		        	</xsl:choose>
				</xsl:otherwise>

		    </xsl:choose>
		</div>
	</div>
</xsl:template>


<xsl:template match="addresses">
	<xsl:for-each select="*">
		<xsl:value-of select="."/><br/>
	</xsl:for-each>
</xsl:template>


<xsl:template match="cat_data">
	<table class="keyvalue">
	<xsl:for-each select="*">
		<tr>
            <td class="key" style="vertical-align:top">
                <xsl:choose>
                    <xsl:when test="@title">
                        <xsl:value-of select="@title"/>:
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="local-name()"/>:
                    </xsl:otherwise>
                </xsl:choose>
			</td>
            <td class="value">
                <xsl:choose>
                    <xsl:when test="local-name() = 'description'">
                        <xsl:call-template name="nl2br">
                            <xsl:with-param name="text" select="." />
                        </xsl:call-template>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:choose>
                            <xsl:when test="count(current()/*) > 1">
                                <xsl:choose>
                                    <xsl:when test="starts-with(local-name(), 'f_popup')">
                                        <table cellspacing="0" class="listing content_table">
                                            <tbody>
                                                <xsl:for-each select="./*">
                                                    <xsl:if test="@title != 'reportId'">
                                                        <tr>
                                                        <xsl:for-each select="@*">
                                                            <!-- See  ID-6870  this condition seems to remove a lot of data for viewing. -->
                                                            <!-- <xsl:if test="name() != 'title'"> -->
                                                            <td><xsl:value-of select="." /></td>
                                                            <!-- </xsl:if> -->
                                                        </xsl:for-each>
                                                        </tr>
                                                    </xsl:if>
                                                </xsl:for-each>
                                            </tbody>
                                        </table>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <ul>
                                            <xsl:for-each select="*">
                                                <li>- <xsl:value-of select="."/></li>
                                            </xsl:for-each>
                                        </ul>
                                    </xsl:otherwise>
                                </xsl:choose>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:call-template name="nl2br">
                                    <xsl:with-param name="text" select="." />
                                </xsl:call-template>
                            </xsl:otherwise>
                        </xsl:choose>
                    </xsl:otherwise>
                </xsl:choose>
            </td>
		</tr>
	</xsl:for-each>
	</table>
</xsl:template>

<xsl:template name="cat_data_multi">
    <table cellspacing="0" class="listing content_table">
        <thead>
        <xsl:for-each select="cat_data[1]">
            <xsl:for-each select="*">
                <xsl:choose>
                    <xsl:when test="local-name() = 'description'"></xsl:when>
                    <xsl:otherwise>
                        <th>
                            <xsl:value-of select="@title"/>
                        </th>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:for-each>
        </xsl:for-each>
        </thead>
        <tbody>
        <xsl:for-each select="cat_data">
            <tr>
                <xsl:for-each select="*">
                    <xsl:choose>
                        <xsl:when test="local-name() = 'description'">
                            <xsl:call-template name="nl2br">
                            </xsl:call-template>
                        </xsl:when>
                        <xsl:otherwise>
                            <td style="vertical-align:top">
                                <xsl:choose>
                                    <xsl:when test="count(current()/*) > 1">
                                        <ul>
                                            <xsl:for-each select="*">
                                                <li>- <xsl:value-of select="."/></li>
                                            </xsl:for-each>
                                        </ul>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <xsl:value-of select="."/>
                                    </xsl:otherwise>
                                </xsl:choose>
                            </td>
                        </xsl:otherwise>
                    </xsl:choose>

                </xsl:for-each>
            </tr>
        </xsl:for-each>
        </tbody>
    </table>
</xsl:template>


<xsl:template name="location">
	<xsl:param name="loc" />

	<xsl:variable name="parent">
		<xsl:value-of select="/isys_export/objects/object[id = $loc]/data/category[@const = 'C__CATG__LOCATION']/cat_data/parent/@id"/>
	</xsl:variable>

	<xsl:choose>
		<xsl:when test="/isys_export/objects/object[id = $parent]">
			<xsl:call-template name="location">
	          	<xsl:with-param name="loc" select="/isys_export/objects/object[id = $loc]/data/category[@const = 'C__CATG__LOCATION']/cat_data/parent/@id" />
	        </xsl:call-template>
	    	> <xsl:value-of select="/isys_export/objects/object[id = $loc]/data/category[@const = 'C__CATG__LOCATION']/cat_data/parent"/>
	    </xsl:when>
	    <xsl:otherwise>
			<xsl:value-of select="/isys_export/objects/object[id = $loc]/data/category[@const = 'C__CATG__LOCATION']/cat_data/parent/@location_path"/>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>


<xsl:template name="nl2br">
	<xsl:param name="text"/>
	<xsl:choose>
		<xsl:when test="contains($text,'&#xA;')">
			<xsl:value-of select="substring-before($text,'&#xA;')" disable-output-escaping="yes"/>
			<br/>
			<xsl:call-template name="nl2br">
				<xsl:with-param name="text" select="substring-after($text,'&#xA;')"/>
			</xsl:call-template>
		</xsl:when>
		<xsl:otherwise>
			<xsl:value-of select="$text" disable-output-escaping="yes"/>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>


</xsl:stylesheet>
