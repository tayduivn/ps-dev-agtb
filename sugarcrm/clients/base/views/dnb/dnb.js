/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Views.Base.DnbView
 * @alias SUGAR.App.view.views.BaseDnbView
 * @extends View.View
 */
({
    plugins: ['Dashlet'],
    currentCompany: null,
    //dnb api response codes
    responseCodes: {
        success: 'CM000'
    },
    //common constants
    commonConst: {
        'sic_code': 399,
        'hoovers_ind_code': 25838,
        'sic_to_hic': 3599
    },

    //mapping of sugar column names to dnb api response
    accountsMap: {
        'name': 'OrganizationName.OrganizationPrimaryName.0.OrganizationName.$', //account name
        'duns_num': 'SubjectHeader.DUNSNumber', //duns_num
        'billing_address_street': 'Location.PrimaryAddress.0.StreetAddressLine.0.LineText',
        'billing_address_city': 'Location.PrimaryAddress.0.PrimaryTownName',
        'billing_address_state': 'Location.PrimaryAddress.0.TerritoryAbbreviatedName',
        'billing_address_country': 'Location.PrimaryAddress.0.CountryISOAlpha2Code',
        'billing_address_postalcode': 'Location.PrimaryAddress.0.PostalCode',
        'website': 'Telecommunication.WebPageAddress.0.TelecommunicationAddress',
        'phone_office': 'Telecommunication.TelephoneNumber.0.TelecommunicationNumber',
        'employees': 'EmployeeFigures.IndividualEntityEmployeeDetails.TotalEmployeeQuantity',
        'annual_revenue': 'Financial.KeyFinancialFiguresOverview.0.SalesRevenueAmount.0.$',
        'ownership': 'OrganizationDetail.ControlOwnershipTypeText.$',
        'sic_code': 'primarySIC.IndustryCode.$'
    },
    //D&B Firmographic API product codes
    compInfoProdCD: {
        'lite': 'CST_PRD_1',
        'std': 'DCP_STD',
        'prem': 'DCP_PREM'
    },

    /**
     * json_path -- refers to the json path to traverse to obtain the data element
     * label -- refers to the label to be used to name the data element
     * desc -- refers to the label used to describe the data element
     * case_fmt -- boolean -- when true specifies that this data element needs to be formatted to proper case
     * sub_array -- refers to meta data for nested json objects
     */
    compinfoDD: {
        'compname': {
            'json_path': 'OrganizationName.OrganizationPrimaryName.0.OrganizationName.$',
            'label': 'LBL_DNB_PRIM_NAME',
            'desc': 'LBL_DNB_PRIM_NAME_DESC',
            'case_fmt': true
        },
        'tradename': {
            'json_path': 'OrganizationName.TradeStyleName.0.OrganizationName.$',
            'label': 'LBL_DNB_TRD_NAME',
            'desc': 'LBL_DNB_TRD_NAME_DESC',
            'case_fmt': true
        },
        'locationtype': {
            'json_path': 'OrganizationDetail.FamilyTreeMemberRole.0.FamilyTreeMemberRoleText.$',
            'label': 'LBL_DNB_LOCATION_TYPE',
            'desc': 'LBL_DNB_LOCATION_TYPE_DESC',
            'case_fmt': true
        },
        'cntrowndate': {
            'json_path': 'OrganizationDetail.ControlOwnershipDate.$',
            'label': 'LBL_DNB_CNTRL_OWN_DATE',
            'desc': 'LBL_DNB_CNTRL_OWN_DATE_DESC'
        },
        'cntrowntype': {
            'json_path': 'OrganizationDetail.ControlOwnershipTypeText.$',
            'label': 'LBL_DNB_CNTRL_TYP_TEXT',
            'desc': 'LBL_DNB_CNTRL_TYP_TEXT_DESC',
            'case_fmt': true
        },
        'operstatus': {
            'json_path': 'OrganizationDetail.OperatingStatusText.$',
            'label': 'LBL_DNB_OPERL_STA_TEXT',
            'desc': 'LBL_DNB_OPERL_STA_TEXT_DESC',
            'case_fmt': true
        },
        'boneyardind': {
            'json_path': 'OrganizationDetail.BoneyardOrganizationIndicator',
            'label': 'LBL_DNB_BONE_ORG_IND',
            'desc': 'LBL_DNB_BONE_ORG_IND_DESC'
        },
        'orgstartyear': {
            'json_path': 'OrganizationDetail.OrganizationStartYear',
            'label': 'LBL_DNB_ORGS_STRT_YEAR',
            'desc': 'LBL_DNB_ORGS_STRT_YEAR_DESC'
        },
        'francoper': {
            'json_path': 'OrganizationDetail.FranchiseOperationTypeText.$',
            'label': 'LBL_DNB_FRAN_TYP_TEXT',
            'desc': 'LBL_DNB_FRAN_TYP_TEXT_DESC',
            'case_fmt': true
        },
        'primaddrstreet': {
            'json_path': 'Location.PrimaryAddress.0.StreetAddressLine.0.LineText',
            'label': 'LBL_DNB_PRIM_STREET',
            'desc': 'LBL_DNB_PRIM_STREET_DESC',
            'case_fmt': true
        },
        'primaddrcity': {
            'json_path': 'Location.PrimaryAddress.0.PrimaryTownName',
            'label': 'LBL_DNB_PRIM_CITY',
            'desc': 'LBL_DNB_PRIM_CITY_DESC',
            'case_fmt': true
        },
        'primaddrstateabbr': {
            'json_path': 'Location.PrimaryAddress.0.TerritoryAbbreviatedName',
            'label': 'LBL_DNB_PRIM_STATE_ABBR',
            'desc': 'LBL_DNB_PRIM_STATE_ABBR_DESC'
        },
        'primaddrstate': {
            'json_path': 'Location.PrimaryAddress.0.TerritoryOfficialName',
            'label': 'LBL_DNB_PRIM_STATE',
            'desc': 'LBL_DNB_PRIM_STATE_DESC',
            'case_fmt': true
        },
        'primaddrctrycd': {
            'json_path': 'Location.PrimaryAddress.0.CountryISOAlpha2Code',
            'label': 'LBL_DNB_PRIM_CTRY_CD',
            'desc': 'LBL_DNB_PRIM_CTRY_CD_DESC'
        },
        'primaddrctrygrp': {
            'json_path': 'Location.PrimaryAddress.0.CountryGroupName',
            'label': 'LBL_DNB_PRIM_CTRY_GRP',
            'desc': 'LBL_DNB_PRIM_CTRY_GRP_DESC'
        },
        'primaddrzip': {
            'json_path': 'Location.PrimaryAddress.0.PostalCode',
            'label': 'LBL_DNB_PRIM_ZIP',
            'desc': 'LBL_DNB_PRIM_ZIP_DESC'
        },
        'primaddrcountyname': {
            'json_path': 'Location.PrimaryAddress.0.CountyOfficialName',
            'label': 'LBL_DNB_PRIM_COUNTY_NAME',
            'desc': 'LBL_DNB_PRIM_COUNTY_NAME_DESC'
        },
        'uscensuscd': {
            'json_path': 'Location.PrimaryAddress.0.MetropolitanStatisticalAreaUSCensusCode.0',
            'label': 'LBL_DNB_PRIM_CEN_CD',
            'desc': 'LBL_DNB_PRIM_CEN_CD_DESC'
        },
        'mailingaddrstreet': {
            'json_path': 'Location.MailingAddress.0.StreetAddressLine.0.LineText',
            'label': 'LBL_DNB_MAIL_STREET',
            'desc': 'LBL_DNB_PRIM_STREET_DESC',
            'case_fmt': true
        },
        'mailingaddrcity': {
            'json_path': 'Location.MailingAddress.0.PrimaryTownName',
            'label': 'LBL_DNB_MAIL_CITY',
            'desc': 'LBL_DNB_PRIM_CITY_DESC',
            'case_fmt': true
        },
        'mailingaddrstateabbr': {
            'json_path': 'Location.MailingAddress.0.TerritoryAbbreviatedName',
            'label': 'LBL_DNB_MAIL_STATE_ABBR',
            'desc': 'LBL_DNB_PRIM_STATE_ABBR_DESC'
        },
        'mailingaddrzip': {
            'json_path': 'Location.MailingAddress.0.PostalCode',
            'label': 'LBL_DNB_MAIL_ZIP',
            'desc': 'LBL_DNB_PRIM_ZIP_DESC'
        },
        'mailingaddrctrycd': {
            'json_path': 'Location.MailingAddress.0.CountryISOAlpha2Code',
            'label': 'LBL_DNB_MAIL_CTRY_CD',
            'desc': 'LBL_DNB_PRIM_CTRY_CD_DESC'
        },
        'long': {
            'json_path': 'Location.PrimaryAddress.0.LongitudeMeasurement',
            'label': 'LBL_DNB_LAT',
            'desc': 'LBL_DNB_LAT_DESC'
        },
        'lat': {
            'json_path': 'Location.PrimaryAddress.0.LatitudeMeasurement',
            'label': 'LBL_DNB_LONG',
            'desc': 'LBL_DNB_LONG_DESC'
        },
        'phone': {
            'json_path': 'Telecommunication.TelephoneNumber.0.TelecommunicationNumber',
            'label': 'LBL_DNB_PHONE',
            'desc': 'LBL_DNB_PHONE_DESC'
        },
        'fax': {
            'json_path': 'Telecommunication.FacsimileNumber.0.TelecommunicationNumber',
            'label': 'LBL_DNB_FAX',
            'desc': 'LBL_DNB_FAX_DESC'
        },
        'webpage': {
            'json_path': 'Telecommunication.WebPageAddress.0.TelecommunicationAddress',
            'label': 'LBL_DNB_WEBPAGE',
            'desc': 'LBL_DNB_WEBPAGE_DESC',
            'type': 'link'
        },
        'indempcnt': {
            'json_path': 'EmployeeFigures.IndividualEntityEmployeeDetails.TotalEmployeeQuantity',
            'label': 'LBL_DNB_IND_EMP_CNT',
            'desc': 'LBL_DNB_IND_EMP_CNT_DESC'
        },
        'conempcnt': {
            'json_path': 'EmployeeFigures.ConsolidatedEmployeeDetails.TotalEmployeeQuantity',
            'label': 'LBL_DNB_CON_EMP_CNT',
            'desc': 'LBL_DNB_CON_EMP_CNT_DESC'
        },
        'empdet': {
            'json_path': 'PrincipalsAndManagement.CurrentPrincipal',
            'label': 'LBL_DNB_EMP_DET',
            'desc': 'LBL_DNB_EMP_DET_DESC',
            'sub_array': {
                'data_type': 'emp_det',
                'job_title': 'JobTitle.0.JobTitleText.$',
                'full_name': 'PrincipalName.FullName'
            }
        },
        'lob': {
            'json_path': 'ActivitiesAndOperations.LineOfBusinessDetails.0.LineOfBusinessDescription.$',
            'label': 'LBL_DNB_LOB',
            'desc': 'LBL_DNB_LOB_DESC'
        },
        'impind': {
            'json_path': 'ActivitiesAndOperations.ImportDetails.ImportIndicator',
            'label': 'LBL_DNB_IMP_IND',
            'desc': 'LBL_DNB_IMP_IND_DESC'
        },
        'expind': {
            'json_path': 'ActivitiesAndOperations.ExportDetails.ExportIndicator',
            'label': 'LBL_DNB_EXP_IND',
            'desc': 'LBL_DNB_EXP_IND_DESC'
        },
        'agentind': {
            'json_path': 'ActivitiesAndOperations.SubjectIsAgentDetails.AgentIndicator',
            'label': 'LBL_DNB_AGENT_IND',
            'desc': 'LBL_DNB_AGENT_IND_DESC'
        },
        'opertext': {
            'json_path': 'ActivitiesAndOperations.OperationsText.0',
            'label': 'LBL_DNB_OPER_TEXT',
            'desc': 'LBL_DNB_OPER_TEXT_DESC'
        },
        'histrat': {
            'json_path': 'Assessment.HistoryRatingText.$',
            'label': 'LBL_DNB_HIST_RAT',
            'desc': 'LBL_DNB_HIST_RAT_DESC'
        },
        'ccs': {
            'json_path': 'Assessment.CommercialCreditScore.0.MarketingRiskClassText.$',
            'label': 'LBL_DNB_CCS',
            'desc': 'LBL_DNB_CCS_DESC'
        },
        'uspatriskscr': {
            'json_path': 'Assessment.USPatriotActComplianceRiskScore.ComplianceRiskIndex',
            'label': 'LBL_DNB_USPAT_SCR',
            'desc': 'LBL_DNB_USPAT_SCR_DESC'
        },
        'tpa': {
            'json_path': 'ThirdPartyAssessment.ThirdPartyAssessment',
            'label': 'LBL_DNB_TPA',
            'desc': 'LBL_DNB_TPA_DESC',
            'sub_array': {
                'assmt_type': 'AssessmentTypeValue',
                'assmt': 'AssessmentValue',
                'data_type': 'tpa'
            }
        },
        'minind': {
            'json_path': 'SocioEconomicIdentification.MinorityOwnedIndicator',
            'label': 'LBL_DNB_MIN_IND',
            'desc': 'LBL_DNB_MIN_IND_DESC'
        },
        'smbind': {
            'json_path': 'SocioEconomicIdentification.SmallBusinessIndicator',
            'label': 'LBL_DNB_SMB_IND',
            'desc': 'LBL_DNB_SMB_IND_DESC'
        },
        'ethn': {
            'json_path': 'SocioEconomicIdentification.OwnershipEthnicity.0.EthnicityTypeText.$',
            'label': 'LBL_DNB_ETHN',
            'desc': 'LBL_DNB_ETHN_DESC',
            'case_fmt': true
        },
        'femind': {
            'json_path': 'SocioEconomicIdentification.FemaleOwnedIndicator',
            'label': 'LBL_DNB_FEM_IND',
            'desc': 'LBL_DNB_FEM_IND_DESC'
        },
        'smbdisadv': {
            'json_path': 'SocioEconomicIdentification.SmallDisadvantagedBusinessIndicator',
            'label': 'LBL_DNB_SMBDISADV_IND',
            'desc': 'LBL_DNB_SMBDISADV_IND_DESC'
        },
        'alasnat': {
            'json_path': 'SocioEconomicIdentification.AlaskanNativeCorporationIndicator',
            'label': 'LBL_DNB_ALASNAT_IND',
            'desc': 'LBL_DNB_ALASNAT_IND_DESC'
        },
        'smbcert': {
            'json_path': 'SocioEconomicIdentification.CertifiedSmallBusinessIndicator',
            'label': 'LBL_DNB_SMB_CERT',
            'desc': 'LBL_DNB_SMB_CERT_DESC'
        },
        'mincoll': {
            'json_path': 'SocioEconomicIdentification.MinorityCollegeIndicator',
            'label': 'LBL_DNB_MIN_COLL',
            'desc': 'LBL_DNB_MIN_COLL_DESC'
        },
        'disab': {
            'json_path': 'SocioEconomicIdentification.DisabledOwnedIndicator',
            'label': 'LBL_DNB_DISAB_IND',
            'desc': 'LBL_DNB_DISAB_IND_DESC'
        },
        'svcdisabvet': {
            'json_path': 'SocioEconomicIdentification.ServiceDisabledVeteranOwnedIndicator',
            'label': 'LBL_DNB_SVC_DISAB_VET',
            'desc': 'LBL_DNB_SVC_DISAB_VET_DESC'
        },
        'vietvet': {
            'json_path': 'SocioEconomicIdentification.VietnamVeteranOwnedIndicator',
            'label': 'LBL_DNB_VIET_VET',
            'desc': 'LBL_DNB_VIET_VET_DESC'
        },
        'airprtdisadvent': {
            'json_path': 'SocioEconomicIdentification.AirportConcessionDisadvantagedBusinessEnterpriseIndicator',
            'label': 'LBL_DNB_AIRPRT_DISADV_ENT',
            'desc': 'LBL_DNB_AIRPRT_DISADV_ENT_DESC'
        },
        'disabvetent': {
            'json_path': 'SocioEconomicIdentification.DisabledVeteranBusinessEnterpriseIndicator',
            'label': 'LBL_DNB_DISAB_VET_ENT',
            'desc': 'LBL_DNB_DISAB_VET_ENT_DESC'
        },
        'disadvent': {
            'json_path': 'SocioEconomicIdentification.DisadvantagedBusinessEnterpriseIndicator',
            'label': 'LBL_DNB_DISADV_ENT',
            'desc': 'LBL_DNB_DISADV_ENT_DESC'
        },
        'disadvvetent': {
            'json_path': 'SocioEconomicIdentification.DisadvantagedVeteranEnterpriseIndicator',
            'label': 'LBL_DNB_DISADV_VET_ENT',
            'desc': 'LBL_DNB_DISADV_VET_ENT_DESC'
        },
        'minent': {
            'json_path': 'SocioEconomicIdentification.MinorityBusinessEnterpriseIndicator',
            'label': 'LBL_DNB_MIN_ENT',
            'desc': 'LBL_DNB_MIN_ENT_DESC'
        },
        'fement': {
            'json_path': 'SocioEconomicIdentification.FemaleOwnedBusinessEnterpriseIndicator',
            'label': 'LBL_DNB_FEM_ENT',
            'desc': 'LBL_DNB_FEM_ENT_DESC'
        },
        'hubcrt': {
            'json_path': 'SocioEconomicIdentification.HUBZoneCertifiedBusinessIndicator',
            'label': 'LBL_DNB_HUB_CRT',
            'desc': 'LBL_DNB_HUB_CRT_DESC'
        },
        'eightacrt': {
            'json_path': 'SocioEconomicIdentification.EightACertifiedBusinessIndicator',
            'label': 'LBL_DNB_EIGHTA_CRT',
            'desc': 'LBL_DNB_EIGHTA_CRT_DESC'
        },
        'vet_ind': {
            'json_path': 'SocioEconomicIdentification.VeteranOwnedIndicator',
            'label': 'LBL_DNB_VET_IND',
            'desc': 'LBL_DNB_VET_IND_DESC'
        },
        'lsind': {
            'json_path': 'SocioEconomicIdentification.LaborSurplusAreaIndicator',
            'label': 'LBL_DNB_LS_IND',
            'desc': 'LBL_DNB_LS_IND_DESC'
        },
        'vetent': {
            'json_path': 'SocioEconomicIdentification.VeteranBusinessEnterpriseIndicator',
            'label': 'LBL_DNB_VET_ENT',
            'desc': 'LBL_DNB_VET_ENT_DESC'
        },
        'inqcnt': {
            'json_path': 'SubjectHeader.TotalInquiriesCount',
            'label': 'LBL_DNB_INQ_CNT',
            'desc': 'LBL_DNB_INQ_CNT_DESC'
        },
        'transferdunsnbr': {
            'json_path': 'SubjectHeader.TransferDUNSNumberRegistration.0.TransferredFromDUNSNumber',
            'label': 'LBL_DNB_TRNS_DUNS',
            'desc': 'LBL_DNB_TRNS_DUNS_DESC'
        },
        'lastupddate': {
            'json_path': 'SubjectHeader.LastUpdateDate.$',
            'label': 'LBL_DNB_LAST_UPD_DATE',
            'desc': 'LBL_DNB_LAST_UPD_DATE_DESC'
        },
        'marketind': {
            'json_path': 'SubjectHeader.MarketabilityIndicator',
            'label': 'LBL_DNB_MARKET_IND',
            'desc': 'LBL_DNB_MARKET_IND_DESC'
        },
        'dunsselfind': {
            'json_path': 'SubjectHeader.DUNSSelfRequestIndicator',
            'label': 'LBL_DNB_DUNSSELF_IND',
            'desc': 'LBL_DNB_DUNSSELF_IND_DESC'
        },
        'nonmarkreastxt': {
            'json_path': 'SubjectHeader.NonMarketableReasonText.0.$',
            'label': 'LBL_DNB_NONMARK_REAS_TXT',
            'desc': 'LBL_DNB_NONMARK_REAS_TXT_DESC',
            'case_fmt': true
        },
        'indcodes': {
            'json_path': 'IndustryCode.IndustryCode',
            'label': 'LBL_DNB_IND_CD',
            'desc': 'LBL_DNB_IND_CD_DESC',
            'sub_array': {
                'data_type': 'ind_codes',
                'ind_code_type': '@TypeText',
                'ind_code': 'IndustryCode.$',
                'ind_code_desc': 'IndustryCodeDescription.0.$',
                'disp_seq': 'DisplaySequence'
            }
        }
    },

    /*
     * @property {Object} searchDD Data Dictionary For D&B Search API Response
     */
    searchDD: {
        'companyname': {
            'json_path': 'OrganizationPrimaryName.OrganizationName.$',
            'case_fmt': true
        },
        'dunsnum': {
            'json_path': 'DUNSNumber'
        },
        'locationtype': {
            'json_path': 'locationtype',
            'case_fmt': true
        },
        'streetaddr': {
            'json_path': 'PrimaryAddress.StreetAddressLine.0.LineText',
            'case_fmt': true
        },
        'town': {
            'json_path': 'PrimaryAddress.PrimaryTownName',
            'case_fmt': true
        },
        'territory': {
            'json_path': 'PrimaryAddress.TerritoryOfficialName',
            'case_fmt': true
        },
        'ctrycd': {
            'json_path': 'PrimaryAddress.CountryISOAlpha2Code'
        },
        'isDupe': {
            'json_path': 'isDupe'
        }

    },
    accountsDD: null,
    //dnb append service json paths
    appendSVCPaths: {
        'responseCode': 'OrderProductResponse.TransactionResult.ResultID',
        'responseMsg': 'OrderProductResponse.TransactionResult.ResultText',
        'industry': 'OrderProductResponse.OrderProductResponseDetail.Product.Organization.IndustryCode.IndustryCode',
        'product': 'OrderProductResponse.OrderProductResponseDetail.Product.Organization',
        'duns': 'OrderProductResponse.OrderProductResponseDetail.InquiryDetail.DUNSNumber'
    },
    //common json paths
    commonJSONPaths: {
        'industryCode': 'IndustryCode.$',
        'industryType': '@DNBCodeValue',
        'srchRespCode': 'FindCompanyResponse.TransactionResult.ResultID',
        'srchRespMsg': 'FindCompanyResponse.TransactionResult.ResultText',
        'srchRslt': 'FindCompanyResponse.FindCompanyResponseDetail.FindCandidate',
        'competitors': 'FindCompetitorResponse.FindCompetitorResponseDetail.Competitor',
        'industryprofile': 'OrderProductResponse.OrderProductResponseDetail.Product.IndustryProfile'
    },
    //common error codes with error labels
    commonErrorMap: {
        'ERROR_DNB_CONFIG': 'LBL_DNB_NOT_CONFIGURED',
        'ERROR_CURL_5': 'LBL_DNB_ERROR_CURL_RESOLVE_PROXY',
        'ERROR_CURL_6': 'LBL_DNB_ERROR_CURL_RESOLVE_HOST',
        'ERROR_CURL_7': 'LBL_DNB_ERROR_CURL_CONNECTION_FAIL',
        'ERROR_CURL_56': 'LBL_DNB_ERROR_CURL_NETWORK_FAIL',
        'ERROR_DNB_SVC_ERR': 'LBL_DNB_SVC_ERR',
        'ERROR_DNB_UNKNOWN': 'LBL_DNB_UNKNOWN_ERROR',
        'ERROR_EMPTY_PARAM': 'LBL_DNB_EMPTY_PARAM',
        'ERROR_BAD_REQUEST': 'EXCEPTION_MISSING_PARAMTER',
        'ERROR_INVALID_MODULE_NAME': 'LBL_DNB_INVALID_MODULE_NAME'
    },
    //formatting functions map
    formatTypeMap: null,

    //dashlet initialize
    initDashlet: function() {
        this.accountsDD = {
            'name': this.compinfoDD.compname,
            'billing_address_street': this.compinfoDD.primaddrstreet,
            'billing_address_city': this.compinfoDD.primaddrcity,
            'billing_address_state': this.compinfoDD.primaddrstateabbr,
            'billing_address_country': this.compinfoDD.primaddrctrycd,
            'billing_address_postalcode': this.compinfoDD.primaddrzip,
            'website': this.compinfoDD.webpage,
            'phone_office': this.compinfoDD.phone,
            'employees': this.compinfoDD.indempcnt,
            'annual_revenue': {
                'json_path': 'Financial.KeyFinancialFiguresOverview.0',
                'sub_object': {
                    'data_type': 'sales_rev',
                    'units': 'SalesRevenueAmount.0.@UnitOfSize',
                    'currency_cd': 'SalesRevenueAmount.0.@CurrencyISOAlpha3Code',
                    'financial_yr': 'StatementHeaderDetails.FinancialStatementToDate.$',
                    'amount': 'SalesRevenueAmount.0.$',
                    'label': 'LBL_DNB_SALES_REVENUE'
                }
            },
            'ownership': this.compinfoDD.cntrowntype,
            'sic_code': {
                'json_path': 'IndustryCode.IndustryCode',
                'sub_object': {
                    'data_type': 'prim_sic',
                    'sic_type_code' : 399,
                    'ind_code': 'IndustryCode.$',
                    'label': 'LBL_DNB_SIC'
                }
            }
        };
        this.formatTypeMap = {
            'emp_det': this.formatEmployeeDet,
            'ind_codes': this.formatIndCodes,
            'tpa': this.formatTPA,
            'sales_rev': this.formatAnnualSales,
            'prim_sic': this.formatPrimSic
        };
    },

    /**
     * Check if a particular json path is valid
     * @param {Object} obj
     * @param {String} path
     * @return {Boolean}
     */
    checkJsonNode: function(obj, path) {
        var args = path.split('.');
        for (var i = 0; i < args.length; i++) {
            if (_.isNull(obj) || _.isUndefined(obj) || !obj.hasOwnProperty(args[i])) {
                return false;
            }
            obj = obj[args[i]];
        }
        return true;
    },

    /**
     * Checks and Process the error
     * @param {Object} xhr
     * @param {Object} status
     * @param {Object} error
     */
    checkAndProcessError: function(xhr, status, error) {
        if (this.disposed) {
            return;
        }
        var resultData = { 'product': null, 'errmsg': null };
        app.logger.error('xhr code is:' + xhr.code);
        var errorCode = xhr.code;
        if (xhr.code) {
            resultData = { 'errmsg': this.commonErrorMap[errorCode] };
        }
        if (this.name === 'dnb-account-create') {
            this.template = app.template.get('dnb.dnb-acct-create-error');
        } else {
            this.template = app.template.get('dnb.dnb-error');
        }
        _.extend(this, resultData);
        this.render();
        this.$('div#error-display').show();
        this.$('.showLessData').hide();
    },

    /**
     * Check if a particular json path is valid and returns value if valid else return nothing
     * @param {Object} obj
     * @param {String} path
     * @return {Array|Object|null}
     */
    getJsonNode: function(obj, path) {
        var args = path.split('.');
        for (var i = 0; i < args.length; i++) {
            if (_.isNull(obj) || _.isUndefined(obj) || !obj.hasOwnProperty(args[i])) {
                return;
            }
            obj = obj[args[i]];
        }
        return obj;
    },

    /**
     * Company search based on keyword
     * @param {String} searchString
     * @param {Function} renderFunction, a function to be called to render the search results
     */
    baseCompanySearch: function(searchString, renderFunction) {
        //adding the '*' t0 searchString for wildcard search
        searchString = searchString + '*';
        searchString = encodeURI(searchString);
        var srchResults = {'companies': null, 'errmsg': null};
        var dnbSearchUrl = app.api.buildURL('connector/dnb/search/q=' + searchString, '', {}, {});
        var self = this;
        app.api.call('READ', dnbSearchUrl, {}, {
            success: function(data) {
                var responseCode = self.getJsonNode(data, self.commonJSONPaths.srchRespCode), responseMsg = self.getJsonNode(data, self.commonJSONPaths.srchRespMsg);
                if (responseCode && responseCode === self.responseCodes.success) {
                    srchResults.companies = self.getJsonNode(data, self.commonJSONPaths.srchRslt);
                    //assigning location type
                    _.each(srchResults.companies, function(companyObj) {
                        if (companyObj.FamilyTreeMemberRole) {
                            //we are relying on DNBCodeValue
                            //higher the code value more the precedence in the family tree role
                            //hence we are using the _.max function
                            var locationType = _.max(companyObj.FamilyTreeMemberRole, function(memberRole) {
                                return memberRole.FamilyTreeMemberRoleText['@DNBCodeValue'];
                            });
                            //if the location type is parent then we need not display it
                            if (locationType.FamilyTreeMemberRoleText['$'] !== 'Parent') {
                                companyObj.locationtype = locationType.FamilyTreeMemberRoleText['$'];
                            }
                        }
                    });
                    self.companyList = srchResults.companies;
                } else {
                    // Normalize no data message to sugar label.
                    if (responseCode === 'CM018') {
                        responseMsg = app.lang.get('LBL_NO_DATA_AVAILABLE');
                    }
                    srchResults.errmsg = responseMsg || app.lang.get('LBL_DNB_SVC_ERR');
                }
                renderFunction.call(self, srchResults);
            },
            error: _.bind(self.checkAndProcessError, self)
        });
    },

    /**
     * Gets company information for a DUNS number
     * @param {String} duns_num -- duns_num of the company
     * @param {String} prod_code -- CST_PRD_1 or DCP_STD or DCP_PREM (referring to the 3 types of comp info dashlets)
     * @param {String} backToListLabel -- label to be rendered to redirect to the previous view
     * @param {Function} renderFunction -- a function to be called to render the dnbapiresponse
     */
    baseCompanyInformation: function(duns_num, prod_code, backToListLabel, renderFunction) {
        var self = this;
        var firmoParams = {
            'duns_num': duns_num,
            'prod_code': prod_code
        };
        var cacheKey = 'dnb:' + firmoParams.duns_num + ':' + firmoParams.prod_code;
        var cacheContent = app.cache.get(cacheKey);
        if (cacheContent) {
            var resultData = cacheContent;
            if (backToListLabel) {
                resultData.backToListLabel = backToListLabel;
            }
            renderFunction.call(self, resultData);
        } else {
            var dnbProfileUrl = app.api.buildURL('connector/dnb/firmographic', '', {}, {}), resultData = {'product': null, 'errmsg': null, 'backToListLabel': null};
            app.api.call('create', dnbProfileUrl, {'qdata': firmoParams}, {
                success: function(data) {
                    var responseCode = self.getJsonNode(data, self.appendSVCPaths.responseCode), responseMsg = self.getJsonNode(data, self.appendSVCPaths.responseMsg);
                    if (!_.isUndefined(responseCode) && responseCode === 'CM000') {
                        resultData.product = data;
                        //if primary sic is available set it
                        //TO DO: move to js preprocessing
                        // as a part of the handlebars normalization
                        var industryCodeArray = self.getJsonNode(data, self.appendSVCPaths.industry);
                        if (!_.isUndefined(industryCodeArray)) {
                            //399 is the industry code type value for US SIC
                            resultData.product.primarySIC = self.getPrimaryIndustry(industryCodeArray, self.commonConst.sic_code);
                        }
                        app.cache.set(cacheKey, resultData);
                    } else {
                        resultData.errmsg = responseMsg || app.lang.get('LBL_DNB_SVC_ERR');
                    }
                    if (!_.isUndefined(backToListLabel)) {
                        resultData.backToListLabel = backToListLabel;
                    }
                    renderFunction.call(self, resultData);
                },
                error: _.bind(self.checkAndProcessError, self)
            });
        }
    },

    /**
     * Gets the primary industry code from the array of industry codes
     * @param {Array} industryArray
     * @param {String} industryCode
     * @return {Object}
     */
    getPrimaryIndustry: function(industryArray, industryCode) {
        return _.find(industryArray, function(industryObj) {
            return industryObj['@DNBCodeValue'] === industryCode && industryObj['DisplaySequence'] === 1;
        });
    },

    /**
     * Preprocessing company information / handlebars normalization
     * @param  {Object} firmoResponse -- DNB API Response for Firmographics
     * @param  {Object} dataElementsMap -- Data Elements Map
     * @return {Array}  -- to be passed to the hbs file
     */
    formatCompanyInfo: function(firmoResponse, dataElementsMap) {
        var productDetails = this.getJsonNode(firmoResponse, this.appendSVCPaths.product);
        var formattedDataElements = [];
        if (productDetails) {
            //iterate thru the compinfo map
            _.each(dataElementsMap, function(value, key) {
                //extract the informtaion
                var dnbDataElement = null;
                //if the data map is array then traverse the nested array
                if (value.sub_array) {
                    dnbDataElement = this.getJsonNode(productDetails, value.json_path);
                    _.each(dnbDataElement, function(dnbSubData) {
                        var dnbDataObj = this.formatTypeMap[value.sub_array.data_type].call(this, dnbSubData, value.sub_array);
                        if (!_.isNull(dnbDataObj)) {
                            formattedDataElements.push(dnbDataObj);
                        }
                    }, this);
                } else if (value.sub_object) {
                    dnbDataElement = this.getJsonNode(productDetails, value.json_path);
                    var dnbDataObj = this.formatTypeMap[value.sub_object.data_type].call(this, dnbDataElement, value.sub_object);
                    if (!_.isNull(dnbDataObj)) {
                        formattedDataElements.push(dnbDataObj);
                    }
                } else {
                    //else it is a straightforward data element
                    dnbDataElement = this.getJsonNode(productDetails, value.json_path);
                    var dnbDataObj = {};
                    //populate a json object
                    if (dnbDataElement) {
                        if (value.case_fmt) {
                            dnbDataElement = this.properCase(dnbDataElement);
                        }
                        dnbDataObj.dataElement = dnbDataElement;
                        dnbDataObj.dataLabel = value.label;
                        dnbDataObj.dataName = key;
                        if (value.type === 'link') {
                            dnbDataObj.dataType = 'link';
                        }
                        //push it into an array
                        formattedDataElements.push(dnbDataObj);
                    }
                }
            }, this);
        }
        return formattedDataElements;
    },

    /**
     * Preprocesses employee details
     * @param {Object} employeeObj D&B Current Principal Object
     * @param {Object} empDD Data Dictionary
     * @return {Object}
     */
    formatEmployeeDet: function(employeeObj, empDD) {
        var dnbDataObj = null;
        var jobTitle = this.getJsonNode(employeeObj, empDD.job_title);
        var empName = this.getJsonNode(employeeObj, empDD.full_name);
        if (empName) {
            dnbDataObj = {};
            dnbDataObj.dataElement = this.properCase(empName);
            if (jobTitle) {
                jobTitle = '<i class="icon-user"></i>' + this.properCase(jobTitle);
            } else {
                jobTitle = '<i class="icon-user"></i>' + app.lang.get('LBL_DNB_ASSOCIATE');
            }
            dnbDataObj.dnbLabel = jobTitle;
        }
        return dnbDataObj;
    },

    /**
     * Preprocesses third party assesment (tpa)
     * @param {Object} tpaObj D&B Current Principal Object
     * @param {Object} tpaDD from the Data Dictionary
     * @return {Object} with label and dataelement
     */
    formatTPA: function(tpaObj, tpaDD) {
        var dnbDataObj = null;
        var assmt = this.getJsonNode(tpaObj, tpaDD.assmt);
        var assmt_type = this.getJsonNode(tpaObj, tpaDD.assmt_type);
        if (assmt && assmt_type) {
            dnbDataObj = {};
            dnbDataObj.dataElement = this.properCase(assmt);
            dnbDataObj.dnbLabel = this.properCase(assmt_type);
        }
        return dnbDataObj;
    },

    /**
     * Preprocesses industry code to get primary industry code
     * @param {Array} indCdArr industryCode Array
     * @param {Object} indSicDD industryCode data dic
     * @return {Object} with label and dataelement
     */
    formatPrimSic: function(indCdArr, indSicDD) {
        var dnbDataObj = null, primSicCode = null, primSicObj = this.getPrimaryIndustry(indCdArr, indSicDD.sic_type_code);
        if (primSicObj) {
            primSicCode = this.getJsonNode(primSicObj, indSicDD.ind_code);
            if (primSicCode) {
                dnbDataObj = {};
                dnbDataObj.dataElement = primSicCode;
                dnbDataObj.dataLabel = indSicDD.label;
                dnbDataObj.dataName = 'sic_code';
            }
        }
        return dnbDataObj;
    },

    /**
     * Preprocesses employee details
     * @param {Object} indCodeObj D&B Current Principal Object
     * @param {Object} indDD Data Dictionary
     * @return {Object} with label and dataelement
     */
    formatIndCodes: function(indCodeObj, indDD) {
        var dnbDataObj = null;
        var ind_code_type = this.getJsonNode(indCodeObj, indDD.ind_code_type);
        var ind_code_desc = this.getJsonNode(indCodeObj, indDD.ind_code_desc);
        var ind_code = this.getJsonNode(indCodeObj, indDD.ind_code);
        var disp_seq = this.getJsonNode(indCodeObj, indDD.disp_seq);
        var primaryHTML = '<span class="label label-success pull-right" data-placement="right">' + app.lang.get('LBL_DNB_PRIMARY') + '</span>';
        if (ind_code_desc) {
            dnbDataObj = {};
            // ind_code_desc = this.properCase(ind_code_desc);
            if (disp_seq && disp_seq === 1) {
                dnbDataObj.dataElement = ind_code_desc + primaryHTML;
            } else {
                dnbDataObj.dataElement = ind_code_desc;
            }
            dnbDataObj.dnbLabel = ind_code_type + ':' + ind_code;
        }
        return dnbDataObj;
    },

    /**
     * Preprocessing search results
     * @param {Object} srchResults DNB API Response for search results
     * @param {Object} searchDD Data Elements Map
     * @return {Array} of json objects -- to be passed to the hbs file
     */
    formatSrchRslt: function(srchResults, searchDD) {
        var formattedSrchRslts = [];
        //iterate thru the search results, extract the necessary info
        //populate a js object
        //push it through an array
        _.each(srchResults, function(searchResultObj) {
            var frmtSrchRsltObj = {};
            _.each(searchDD, function(value, key) {
                var dataElement = this.getJsonNode(searchResultObj, value.json_path);
                if (dataElement) {
                    if (value.case_fmt) {
                        dataElement = this.properCase(dataElement);
                    }
                    frmtSrchRsltObj[key] = dataElement;
                }
            }, this);
            formattedSrchRslts.push(frmtSrchRsltObj);
        }, this);
        return formattedSrchRslts;
    },

    /**
     * Preprocesses employee details
     * @param {Object} annsalesObj D&B Current Principal Object
     * @param {Object} annsalesDD Data Dictionary
     * @return {Object} with label and dataelement
     */
    formatAnnualSales: function(annsalesObj, annsalesDD) {
        var dnbDataObj = null;
        var amount = this.getJsonNode(annsalesObj, annsalesDD.amount);
        var units = this.getJsonNode(annsalesObj, annsalesDD.units);
        var currency_cd = this.getJsonNode(annsalesObj, annsalesDD.currency_cd);
        var financial_yr = this.getJsonNode(annsalesObj, annsalesDD.financial_yr);
        if (amount) {
            dnbDataObj = {};
            var finYrHTML = null, unitsStr = null, dnbLabel = '';
            if (financial_yr) {
                finYrHTML = '<span class="label label-success pull-right" data-placement="right">' + financial_yr + '</span>';
            }
            if (units && currency_cd) {
                unitsStr = '(' + app.lang.get('LBL_DNB_IN') + ' ' + units + ' ' + currency_cd + ')';
            }
            dnbDataObj.dataElement = amount;
            dnbLabel = app.lang.get(annsalesDD.label);
            if (unitsStr) {
                dnbLabel = dnbLabel + unitsStr;
            }
            if (finYrHTML) {
                dnbLabel = dnbLabel + finYrHTML;
            }
            dnbDataObj.dnbLabel = dnbLabel;
            dnbDataObj.dataName = 'annual_revenue';
        }
        return dnbDataObj;
    },

    /**
     * Renders the dnb company details for adding companies from dashlets
     * @param {Object} companyDetails dnb api response for company details
     */
    renderCompanyDetails: function(companyDetails) {
        if (this.disposed) {
            return;
        }
        _.extend(this, companyDetails);
        this.render();
        //if there are no company details hide the import button
        if (companyDetails.errmsg) {
            if (this.layout.getComponent('dashlet-toolbar').getField('import_dnb_data')) {
                this.layout.getComponent('dashlet-toolbar').getField('import_dnb_data').getFieldElement().hide();
            }
        } else if (companyDetails.product) {
            if (this.layout.getComponent('dashlet-toolbar').getField('import_dnb_data')) {
                this.layout.getComponent('dashlet-toolbar').getField('import_dnb_data').getFieldElement().show();
            }
            this.currentCompany = companyDetails.product;
        }
        this.$('div#dnb-company-detail-loading').hide();
        this.$('div#dnb-company-details').show();
    },

    /**
     * Renders the dnb company information for Lite, Standard and Premium dashlets
     * @param {Object} companyDetails dnb api response for company details
     */
    renderCompanyInformation: function(companyDetails) {
        if (this.disposed) {
            return;
        }
        this.template = app.template.get('dnb.dnb-comp-info');
        var formattedFirmographics, dnbFirmo = {};
        if (companyDetails.product) {
            formattedFirmographics = this.formatCompanyInfo(companyDetails.product, this.filteredDD);
            dnbFirmo.product = formattedFirmographics;
        }
        if (companyDetails.errmsg) {
            dnbFirmo.errmsg = companyDetails.errmsg;
        }
        this.dnbFirmo = dnbFirmo;
        this.render();
        this.$('div#dnb-compinfo-loading').hide();
        this.$('div#dnb-compinfo-details').show();
        this.$('.showLessData').hide();
    },

    /**
     * Import D&B Accounts Data
     */
    importDNBData: function() {
        var parentModel = this.context.get('model'), accountsModel = this.getAccountsModel(this.currentCompany);
        if (!_.isUndefined(accountsModel)) {
            var self = this;
            app.drawer.open({
                layout: 'create-actions',
                context: {
                    create: true,
                    module: 'Accounts',
                    model: accountsModel
                }
            }, function(accountsModel) {
                if (!accountsModel) {
                    return;
                }
                self.context.resetLoadFlag();
                self.context.set('skipFetch', false);
                self.context.loadData();
            });
        }
    },

    /**
     * Creates and returns an Account bean
     * @param  {Object} companyApiResponse -- obj -- dnb api response for company information
     * @return {Object}
     */
    getAccountsModel: function(companyApiResponse) {
        var organizationDetails = this.getJsonNode(companyApiResponse, this.appendSVCPaths.product);
        var accountsModel = null;
        if (!_.isUndefined(organizationDetails)) {
            var accountsBean = {};
            if (companyApiResponse.primarySIC) {
                organizationDetails.primarySIC = companyApiResponse.primarySIC;
            }
            _.each(this.accountsMap, function(dataElementPath, sugarColumnName) {
                var dnbDataElement = this.getJsonNode(organizationDetails, dataElementPath);
                if (dnbDataElement) {
                    accountsBean[sugarColumnName] = dnbDataElement;
                }
            }, this);
            accountsModel = app.data.createBean('Accounts', accountsBean);
        }
        return accountsModel;
    },

    /**
     * Collapses the dashlet
     */
    collapseDashlet: function() {
        if (this.layout.collapse) {
            this.layout.collapse(true);
        }
    },

    /**
     * Expands the dashlets to reveal more data
     */
    showMoreData: function() {
        this.$('.dnb-show-less').attr('class', 'dnb-show-all');
        this.$('.showLessData').show();
        this.$('.showMoreData').hide();
    },

    /**
     * Truncates the dashlets
     */
    showLessData: function() {
        this.$('.dnb-show-all').attr('class', 'dnb-show-less');
        this.$('.showLessData').hide();
        this.$('.showMoreData').show();
    },

    /**
     * Formats the sales revenue amount
     * @param  {String} amount sales revenue
     * @return {String} formatted string
     */
    formatSalesRevenue: function(amount) {
        amount = amount.toFixed(0).replace(/(\d)(?=(\d{3})+\b)/g, '$1,');
        return amount;
    },

    /**
     * Formats the string to proper case
     * @param {String}  strParam string to convert
     * @return {String}  properCase String
     */
    properCase: function(strParam) {
        //http://stackoverflow.com/a/196991/226906
        return strParam.replace(/\w\S*/g, function(txt) {
            return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
        });
    },

    /**
     * Imports data from dashlet to the sugar crm accounts
     * @param {Boolean} importFlag (When set to true indicates warning message must not be displayed for the first data import
     *                 This requirement was suggested by Wes in the Account creation screen
     *                 Where a user searches for a company name using a keyword
     *                 So when the user imports data for the first time
     *                 It is pretty obvious that he wants to override the keyword with the newly imported data
     *                 setting the flag to false prevents the warning message from being displayed
     *                )
     */
    importAccountsData: function(importFlag) {
        var setModelFlag = true;
        if (_.isUndefined(importFlag)) {
            importFlag = true;
            setModelFlag = false;
        } else if (importFlag) {
            setModelFlag = false;
        }
        var dnbCheckBox = this.$('.dnb_checkbox:checked');
        var accountsModel = this.model;
        // iterate through checkboxes
        // values being overriden stored in updatedData
        // values that are newly being set store in newData
        var updatedData = [], newData = [], dnbPropertyName = null, dnbPropertyValue = null, dnbPropertySelector = null;
        _.each(dnbCheckBox, function(dataElement) {
            dnbPropertyName = dataElement.id;
            if (dnbPropertyName) {
                //we are cloning the selected property value
                //so that we can remove the nested html elements and retain the text alone
                dnbPropertySelector = this.$('#' + dnbPropertyName). parent().siblings('.importData').clone();
                dnbPropertyValue = $.trim(dnbPropertySelector.children().remove().end().text());
                if (!_.isUndefined(accountsModel.get(dnbPropertyName)) && accountsModel.get(dnbPropertyName) !== '' && importFlag) {
                    updatedData.push({propName: dnbPropertyName, propVal: dnbPropertyValue});
                } else if (dnbPropertyValue !== '') {
                    newData.push({propName: dnbPropertyName, propVal: dnbPropertyValue});
                }
            }
        }, this);
        //importing new data
        if (newData.length > 0) {
            this.updateAccountsModel(newData, setModelFlag);
        }
        //update existing data
        if (updatedData.length > 0) {
            var confirmationMsgKey, confirmationMsgData;
            //show a detailed warning message about the single data element being imported
            if (updatedData.length === 1) {
                var fieldName = app.lang.get(accountsModel.fields[updatedData[0].propName].vname, 'Accounts');
                confirmationMsgKey = 'LBL_DNB_DATA_OVERRIDE_SINGLE_FIELD';
                confirmationMsgData = {
                    fieldName: fieldName.toLowerCase(),
                    value: updatedData[0].propVal
                };
            } else {
                var fieldList = [
                    app.lang.get(accountsModel.fields[updatedData[0].propName].vname, 'Accounts').toLowerCase(), app.lang.get(accountsModel.fields[updatedData[1].propName].vname, 'Accounts').toLowerCase()
                ];
                if (updatedData.length === 2) {
                    //list the two fields being imported
                    confirmationMsgKey = 'LBL_DNB_DATA_OVERRIDE_TWO_FIELDS';
                    confirmationMsgData = {
                        fields: fieldList.join(' ' + app.lang.get('LBL_DNB_AND') + ' ')
                    };
                } else {
                    //list the two first fields and append ` and other(s) field(s)`
                    confirmationMsgKey = 'LBL_DNB_DATA_OVERRIDE_MULTIPLE_FIELDS';
                    confirmationMsgData = {
                        fields: fieldList.join(', ')
                    };
                }
            }
            var confirmationMsgTpl = Handlebars.compile(app.lang.get(confirmationMsgKey));
            app.alert.show('dnb-import-warning', {
                level: 'confirmation',
                title: 'LBL_WARNING',
                messages: confirmationMsgTpl(confirmationMsgData),
                onConfirm: _.bind(this.updateAccountsModel, this, updatedData)
            });
        }
    },

    /**
     * Updates the Accounts backbone model with new data
     * @param {Object} updatedData
     * @param {Boolean} setFlag -- true -- just set the updated parameters to the model
     *                    -- false -- save the updated parameters to the model
     */
    updateAccountsModel: function(updatedData, setFlag) {
        var changedAttributes = {};
        // always import the duns_num
        this.model.set('duns_num', this.duns_num);
        if (setFlag) {
            _.each(updatedData, function(updatedAttribute) {
                this.model.set(updatedAttribute.propName, updatedAttribute.propVal);
            }, this);
            app.alert.show('dnb-import-success', { level: 'success',
                title: app.lang.get('LBL_SUCCESS') + ':',
                messages: app.lang.get('LBL_DNB_OVERRIDE_SUCCESS'),
                autoClose: true});
        } else {
            _.each(updatedData, function(updatedAttribute) {
                changedAttributes[updatedAttribute.propName] = updatedAttribute.propVal;
            });
            this.model.save(changedAttributes, {success: function() {
                app.alert.show('dnb-import-success', { level: 'success',
                    title: app.lang.get('LBL_SUCCESS') + ':',
                    messages: app.lang.get('LBL_DNB_OVERRIDE_SUCCESS'),
                    autoClose: true});
            }});
            this.context.loadData();
        }
    },

    /**
     * Filters the data elements for the company information
     */
    baseFilterData: function() {
        this.filteredDD = {};
        _.each(this.compinfoDD, function(value, key) {
            var settingsFlag = this.settings.get(key);
            //if the settings flag is defined and is selected then
            //add that property to the filtered data dictionary
            if (!_.isUndefined(settingsFlag) && settingsFlag === '1') {
                this.filteredDD[key] = value;
            } else if (_.isUndefined(settingsFlag)) {
                //if the settings flag is not defined
                //select it by default
                this.filteredDD[key] = value;
                this.settings.set(key, '1');
            }
        }, this);
    },

    /**
     * Checks the Sugar data base for duplicate duns or contacts
     * @param {Object} dupeCheckParams
     * dupeCheckParams must have the following keys
     * 1.type Possible values are duns,contacts
     * 2.apiResponse
     * 3.module Possible values are findcompany, competitors, cleansematch, familytree, contacts
     * @param renderFunction
     */
    baseDuplicateCheck: function(dupeCheckParams, renderFunction) {
        var dupeCheckURL = app.api.buildURL('connector/dnb/dupecheck', '', {}, {});
        var self = this;
        app.api.call('create', dupeCheckURL, {'qdata': dupeCheckParams}, {
            success: function(data) {
                renderFunction.call(self, {'product': data});
            },
            error: _.bind(self.checkAndProcessError, self)
        });
    },

    /**
     * Toggles the visibility of the import button in the dashlet
     * @param {String} btnName
     * @param {isVisible} visibility
     */
    toggleImportBtn: function(btnName, isVisible) {
        if (this.layout.getComponent('dashlet-toolbar').getField(btnName)) {
            if (isVisible) {
                this.layout.getComponent('dashlet-toolbar').getField(btnName).getFieldElement().show();
                this.layout.getComponent('dashlet-toolbar').getField(btnName).getFieldElement().removeClass('hide');
            } else {
                this.layout.getComponent('dashlet-toolbar').getField(btnName).getFieldElement().hide();
            }
        }
    },

    /**
     * Toggles enabled / disabled state of the button on the dashlet toolbar
     * @param {Boolean} isEnabled
     * @param {String} btnName
     */
    toggleDashletBtn: function(isEnabled, btnName) {
        if (this.layout.getComponent('dashlet-toolbar').getField(btnName)) {
            if (isEnabled) {
                this.layout.getComponent('dashlet-toolbar').getField(btnName).setDisabled(false);
                this.layout.getComponent('dashlet-toolbar').getField(btnName).getFieldElement().removeClass('disabled');
                this.layout.getComponent('dashlet-toolbar').getField(btnName).getFieldElement().removeClass('hide');
            } else {
                this.layout.getComponent('dashlet-toolbar').getField(btnName).setDisabled(true);
                this.layout.getComponent('dashlet-toolbar').getField(btnName).getFieldElement().addClass('disabled');
            }
        }
    }
})
