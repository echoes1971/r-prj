-- MySQL dump 10.16  Distrib 10.1.43-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: rproject
-- ------------------------------------------------------
-- Server version	10.1.43-MariaDB-0ubuntu0.18.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `rproject`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `rproject` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;

USE `rproject`;

--
-- Table structure for table `rprj_companies`
--

DROP TABLE IF EXISTS `rprj_companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_companies` (
  `id` varchar(16) NOT NULL,
  `owner` varchar(16) NOT NULL,
  `group_id` varchar(16) NOT NULL,
  `permissions` char(9) NOT NULL DEFAULT 'rwx------',
  `creator` varchar(16) NOT NULL,
  `creation_date` datetime DEFAULT NULL,
  `last_modify` varchar(16) NOT NULL,
  `last_modify_date` datetime DEFAULT NULL,
  `deleted_by` varchar(16) DEFAULT NULL,
  `deleted_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `father_id` varchar(16) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `street` varchar(255) DEFAULT NULL,
  `zip` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `fk_countrylist_id` varchar(16) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `fax` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `p_iva` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rprj_companies_0` (`id`),
  KEY `rprj_companies_1` (`owner`),
  KEY `rprj_companies_2` (`group_id`),
  KEY `rprj_companies_3` (`creator`),
  KEY `rprj_companies_4` (`last_modify`),
  KEY `rprj_companies_5` (`deleted_by`),
  KEY `rprj_companies_6` (`father_id`),
  KEY `rprj_companies_7` (`fk_countrylist_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_companies`
--

LOCK TABLES `rprj_companies` WRITE;
/*!40000 ALTER TABLE `rprj_companies` DISABLE KEYS */;
/*!40000 ALTER TABLE `rprj_companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_countrylist`
--

DROP TABLE IF EXISTS `rprj_countrylist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_countrylist` (
  `id` varchar(16) NOT NULL,
  `Common_Name` varchar(255) DEFAULT NULL,
  `Formal_Name` varchar(255) DEFAULT NULL,
  `Type` varchar(255) DEFAULT NULL,
  `Sub_Type` varchar(255) DEFAULT NULL,
  `Sovereignty` varchar(255) DEFAULT NULL,
  `Capital` varchar(255) DEFAULT NULL,
  `ISO_4217_Currency_Code` varchar(255) DEFAULT NULL,
  `ISO_4217_Currency_Name` varchar(255) DEFAULT NULL,
  `ITU_T_Telephone_Code` varchar(255) DEFAULT NULL,
  `ISO_3166_1_2_Letter_Code` varchar(255) DEFAULT NULL,
  `ISO_3166_1_3_Letter_Code` varchar(255) DEFAULT NULL,
  `ISO_3166_1_Number` varchar(255) DEFAULT NULL,
  `IANA_Country_Code_TLD` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rprj_countrylist_0` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_countrylist`
--

LOCK TABLES `rprj_countrylist` WRITE;
/*!40000 ALTER TABLE `rprj_countrylist` DISABLE KEYS */;
INSERT INTO `rprj_countrylist` VALUES ('1','Afghanistan','Islamic State of Afghanistan','Independent State','','','Kabul','AFN','Afghani','+93','AF','AFG','004','.af'),('10','Austria','Republic of Austria','Independent State','','','Vienna','EUR','Euro','+43','AT','AUT','040','.at'),('100','Lithuania','Republic of Lithuania','Independent State','','','Vilnius','LTL','Litas','+370','LT','LTU','440','.lt'),('101','Luxembourg','Grand Duchy of Luxembourg','Independent State','','','Luxembourg','EUR','Euro','+352','LU','LUX','442','.lu'),('102','Macedonia','Republic of Macedonia','Independent State','','','Skopje','MKD','Denar','+389','MK','MKD','807','.mk'),('103','Madagascar','Republic of Madagascar','Independent State','','','Antananarivo','MGA','Ariary','+261','MG','MDG','450','.mg'),('104','Malawi','Republic of Malawi','Independent State','','','Lilongwe','MWK','Kwacha','+265','MW','MWI','454','.mw'),('105','Malaysia','','Independent State','','','Kuala Lumpur (legislative/judical) and Putrajaya (administrative)','MYR','Ringgit','+60','MY','MYS','458','.my'),('106','Maldives','Republic of Maldives','Independent State','','','Male','MVR','Rufiyaa','+960','MV','MDV','462','.mv'),('107','Mali','Republic of Mali','Independent State','','','Bamako','XOF','Franc','+223','ML','MLI','466','.ml'),('108','Malta','Republic of Malta','Independent State','','','Valletta','MTL','Lira','+356','MT','MLT','470','.mt'),('109','Marshall Islands','Republic of the Marshall Islands','Independent State','','','Majuro','USD','Dollar','+692','MH','MHL','584','.mh'),('11','Azerbaijan','Republic of Azerbaijan','Independent State','','','Baku','AZN','Manat','+994','AZ','AZE','031','.az'),('110','Mauritania','Islamic Republic of Mauritania','Independent State','','','Nouakchott','MRO','Ouguiya','+222','MR','MRT','478','.mr'),('111','Mauritius','Republic of Mauritius','Independent State','','','Port Louis','MUR','Rupee','+230','MU','MUS','480','.mu'),('112','Mexico','United Mexican States','Independent State','','','Mexico','MXN','Peso','+52','MX','MEX','484','.mx'),('113','Micronesia','Federated States of Micronesia','Independent State','','','Palikir','USD','Dollar','+691','FM','FSM','583','.fm'),('114','Moldova','Republic of Moldova','Independent State','','','Chisinau','MDL','Leu','+373','MD','MDA','498','.md'),('115','Monaco','Principality of Monaco','Independent State','','','Monaco','EUR','Euro','+377','MC','MCO','492','.mc'),('116','Mongolia','','Independent State','','','Ulaanbaatar','MNT','Tugrik','+976','MN','MNG','496','.mn'),('117','Montenegro','Republic of Montenegro','Independent State','','','Podgorica','EUR','Euro','+382','ME','MNE','499','.me and .yu'),('118','Morocco','Kingdom of Morocco','Independent State','','','Rabat','MAD','Dirham','+212','MA','MAR','504','.ma'),('119','Mozambique','Republic of Mozambique','Independent State','','','Maputo','MZM','Meticail','+258','MZ','MOZ','508','.mz'),('12','Bahamas, The','Commonwealth of The Bahamas','Independent State','','','Nassau','BSD','Dollar','+1-242','BS','BHS','044','.bs'),('120','Myanmar (Burma)','Union of Myanmar','Independent State','','','Naypyidaw','MMK','Kyat','+95','MM','MMR','104','.mm'),('121','Namibia','Republic of Namibia','Independent State','','','Windhoek','NAD','Dollar','+264','NA','NAM','516','.na'),('122','Nauru','Republic of Nauru','Independent State','','','Yaren','AUD','Dollar','+674','NR','NRU','520','.nr'),('123','Nepal','','Independent State','','','Kathmandu','NPR','Rupee','+977','NP','NPL','524','.np'),('124','Netherlands','Kingdom of the Netherlands','Independent State','','','Amsterdam (administrative) and The Hague (legislative/judical)','EUR','Euro','+31','NL','NLD','528','.nl'),('125','New Zealand','','Independent State','','','Wellington','NZD','Dollar','+64','NZ','NZL','554','.nz'),('126','Nicaragua','Republic of Nicaragua','Independent State','','','Managua','NIO','Cordoba','+505','NI','NIC','558','.ni'),('127','Niger','Republic of Niger','Independent State','','','Niamey','XOF','Franc','+227','NE','NER','562','.ne'),('128','Nigeria','Federal Republic of Nigeria','Independent State','','','Abuja','NGN','Naira','+234','NG','NGA','566','.ng'),('129','Norway','Kingdom of Norway','Independent State','','','Oslo','NOK','Krone','+47','NO','NOR','578','.no'),('13','Bahrain','Kingdom of Bahrain','Independent State','','','Manama','BHD','Dinar','+973','BH','BHR','048','.bh'),('130','Oman','Sultanate of Oman','Independent State','','','Muscat','OMR','Rial','+968','OM','OMN','512','.om'),('131','Pakistan','Islamic Republic of Pakistan','Independent State','','','Islamabad','PKR','Rupee','+92','PK','PAK','586','.pk'),('132','Palau','Republic of Palau','Independent State','','','Melekeok','USD','Dollar','+680','PW','PLW','585','.pw'),('133','Panama','Republic of Panama','Independent State','','','Panama','PAB','Balboa','+507','PA','PAN','591','.pa'),('134','Papua New Guinea','Independent State of Papua New Guinea','Independent State','','','Port Moresby','PGK','Kina','+675','PG','PNG','598','.pg'),('135','Paraguay','Republic of Paraguay','Independent State','','','Asuncion','PYG','Guarani','+595','PY','PRY','600','.py'),('136','Peru','Republic of Peru','Independent State','','','Lima','PEN','Sol','+51','PE','PER','604','.pe'),('137','Philippines','Republic of the Philippines','Independent State','','','Manila','PHP','Peso','+63','PH','PHL','608','.ph'),('138','Poland','Republic of Poland','Independent State','','','Warsaw','PLN','Zloty','+48','PL','POL','616','.pl'),('139','Portugal','Portuguese Republic','Independent State','','','Lisbon','EUR','Euro','+351','PT','PRT','620','.pt'),('14','Bangladesh','People\'s Republic of Bangladesh','Independent State','','','Dhaka','BDT','Taka','+880','BD','BGD','050','.bd'),('140','Qatar','State of Qatar','Independent State','','','Doha','QAR','Rial','+974','QA','QAT','634','.qa'),('141','Romania','','Independent State','','','Bucharest','RON','Leu','+40','RO','ROU','642','.ro'),('142','Russia','Russian Federation','Independent State','','','Moscow','RUB','Ruble','+7','RU','RUS','643','.ru and .su'),('143','Rwanda','Republic of Rwanda','Independent State','','','Kigali','RWF','Franc','+250','RW','RWA','646','.rw'),('144','Saint Kitts and Nevis','Federation of Saint Kitts and Nevis','Independent State','','','Basseterre','XCD','Dollar','+1-869','KN','KNA','659','.kn'),('145','Saint Lucia','','Independent State','','','Castries','XCD','Dollar','+1-758','LC','LCA','662','.lc'),('146','Saint Vincent and the Grenadines','','Independent State','','','Kingstown','XCD','Dollar','+1-784','VC','VCT','670','.vc'),('147','Samoa','Independent State of Samoa','Independent State','','','Apia','WST','Tala','+685','WS','WSM','882','.ws'),('148','San Marino','Republic of San Marino','Independent State','','','San Marino','EUR','Euro','+378','SM','SMR','674','.sm'),('149','Sao Tome and Principe','Democratic Republic of Sao Tome and Principe','Independent State','','','Sao Tome','STD','Dobra','+239','ST','STP','678','.st'),('15','Barbados','','Independent State','','','Bridgetown','BBD','Dollar','+1-246','BB','BRB','052','.bb'),('150','Saudi Arabia','Kingdom of Saudi Arabia','Independent State','','','Riyadh','SAR','Rial','+966','SA','SAU','682','.sa'),('151','Senegal','Republic of Senegal','Independent State','','','Dakar','XOF','Franc','+221','SN','SEN','686','.sn'),('152','Serbia','Republic of Serbia','Independent State','','','Belgrade','RSD','Dinar','+381','RS','SRB','688','.rs and .yu'),('153','Seychelles','Republic of Seychelles','Independent State','','','Victoria','SCR','Rupee','+248','SC','SYC','690','.sc'),('154','Sierra Leone','Republic of Sierra Leone','Independent State','','','Freetown','SLL','Leone','+232','SL','SLE','694','.sl'),('155','Singapore','Republic of Singapore','Independent State','','','Singapore','SGD','Dollar','+65','SG','SGP','702','.sg'),('156','Slovakia','Slovak Republic','Independent State','','','Bratislava','SKK','Koruna','+421','SK','SVK','703','.sk'),('157','Slovenia','Republic of Slovenia','Independent State','','','Ljubljana','EUR','Euro','+386','SI','SVN','705','.si'),('158','Solomon Islands','','Independent State','','','Honiara','SBD','Dollar','+677','SB','SLB','090','.sb'),('159','Somalia','','Independent State','','','Mogadishu','SOS','Shilling','+252','SO','SOM','706','.so'),('16','Belarus','Republic of Belarus','Independent State','','','Minsk','BYR','Ruble','+375','BY','BLR','112','.by'),('160','South Africa','Republic of South Africa','Independent State','','','Pretoria (administrative), Cape Town (legislative), and Bloemfontein (judical)','ZAR','Rand','+27','ZA','ZAF','710','.za'),('161','Spain','Kingdom of Spain','Independent State','','','Madrid','EUR','Euro','+34','ES','ESP','724','.es'),('162','Sri Lanka','Democratic Socialist Republic of Sri Lanka','Independent State','','','Colombo (administrative/judical) and Sri Jayewardenepura Kotte (legislative)','LKR','Rupee','+94','LK','LKA','144','.lk'),('163','Sudan','Republic of the Sudan','Independent State','','','Khartoum','SDD','Dinar','+249','SD','SDN','736','.sd'),('164','Suriname','Republic of Suriname','Independent State','','','Paramaribo','SRD','Dollar','+597','SR','SUR','740','.sr'),('165','Swaziland','Kingdom of Swaziland','Independent State','','','Mbabane (administrative) and Lobamba (legislative)','SZL','Lilangeni','+268','SZ','SWZ','748','.sz'),('166','Sweden','Kingdom of Sweden','Independent State','','','Stockholm','SEK','Kronoa','+46','SE','SWE','752','.se'),('167','Switzerland','Swiss Confederation','Independent State','','','Bern','CHF','Franc','+41','CH','CHE','756','.ch'),('168','Syria','Syrian Arab Republic','Independent State','','','Damascus','SYP','Pound','+963','SY','SYR','760','.sy'),('169','Tajikistan','Republic of Tajikistan','Independent State','','','Dushanbe','TJS','Somoni','+992','TJ','TJK','762','.tj'),('17','Belgium','Kingdom of Belgium','Independent State','','','Brussels','EUR','Euro','+32','BE','BEL','056','.be'),('170','Tanzania','United Republic of Tanzania','Independent State','','','Dar es Salaam (administrative/judical) and Dodoma (legislative)','TZS','Shilling','+255','TZ','TZA','834','.tz'),('171','Thailand','Kingdom of Thailand','Independent State','','','Bangkok','THB','Baht','+66','TH','THA','764','.th'),('172','Timor-Leste (East Timor)','Democratic Republic of Timor-Leste','Independent State','','','Dili','USD','Dollar','+670','TL','TLS','626','.tp and .tl'),('173','Togo','Togolese Republic','Independent State','','','Lome','XOF','Franc','+228','TG','TGO','768','.tg'),('174','Tonga','Kingdom of Tonga','Independent State','','','Nuku\'alofa','TOP','Pa\'anga','+676','TO','TON','776','.to'),('175','Trinidad and Tobago','Republic of Trinidad and Tobago','Independent State','','','Port-of-Spain','TTD','Dollar','+1-868','TT','TTO','780','.tt'),('176','Tunisia','Tunisian Republic','Independent State','','','Tunis','TND','Dinar','+216','TN','TUN','788','.tn'),('177','Turkey','Republic of Turkey','Independent State','','','Ankara','TRY','Lira','+90','TR','TUR','792','.tr'),('178','Turkmenistan','','Independent State','','','Ashgabat','TMM','Manat','+993','TM','TKM','795','.tm'),('179','Tuvalu','','Independent State','','','Funafuti','AUD','Dollar','+688','TV','TUV','798','.tv'),('18','Belize','','Independent State','','','Belmopan','BZD','Dollar','+501','BZ','BLZ','084','.bz'),('180','Uganda','Republic of Uganda','Independent State','','','Kampala','UGX','Shilling','+256','UG','UGA','800','.ug'),('181','Ukraine','','Independent State','','','Kiev','UAH','Hryvnia','+380','UA','UKR','804','.ua'),('182','United Arab Emirates','United Arab Emirates','Independent State','','','Abu Dhabi','AED','Dirham','+971','AE','ARE','784','.ae'),('183','United Kingdom','United Kingdom of Great Britain and Northern Ireland','Independent State','','','London','GBP','Pound','+44','GB','GBR','826','.uk'),('184','United States','United States of America','Independent State','','','Washington','USD','Dollar','+1','US','USA','840','.us'),('185','Uruguay','Oriental Republic of Uruguay','Independent State','','','Montevideo','UYU','Peso','+598','UY','URY','858','.uy'),('186','Uzbekistan','Republic of Uzbekistan','Independent State','','','Tashkent','UZS','Som','+998','UZ','UZB','860','.uz'),('187','Vanuatu','Republic of Vanuatu','Independent State','','','Port-Vila','VUV','Vatu','+678','VU','VUT','548','.vu'),('188','Vatican City','State of the Vatican City','Independent State','','','Vatican City','EUR','Euro','+379','VA','VAT','336','.va'),('189','Venezuela','Bolivarian Republic of Venezuela','Independent State','','','Caracas','VEB','Bolivar','+58','VE','VEN','862','.ve'),('19','Benin','Republic of Benin','Independent State','','','Porto-Novo','XOF','Franc','+229','BJ','BEN','204','.bj'),('190','Vietnam','Socialist Republic of Vietnam','Independent State','','','Hanoi','VND','Dong','+84','VN','VNM','704','.vn'),('191','Yemen','Republic of Yemen','Independent State','','','Sanaa','YER','Rial','+967','YE','YEM','887','.ye'),('192','Zambia','Republic of Zambia','Independent State','','','Lusaka','ZMK','Kwacha','+260','ZM','ZMB','894','.zm'),('193','Zimbabwe','Republic of Zimbabwe','Independent State','','','Harare','ZWD','Dollar','+263','ZW','ZWE','716','.zw'),('194','Abkhazia','Republic of Abkhazia','Proto Independent State','','','Sokhumi','RUB','Ruble','+995','GE','GEO','268','.ge'),('195','China, Republic of (Taiwan)','Republic of China','Proto Independent State','','','Taipei','TWD','Dollar','+886','TW','TWN','158','.tw'),('196','Nagorno-Karabakh','Nagorno-Karabakh Republic','Proto Independent State','','','Stepanakert','AMD','Dram','+374-97','AZ','AZE','031','.az'),('197','Northern Cyprus','Turkish Republic of Northern Cyprus','Proto Independent State','','','Nicosia','TRY','Lira','+90-392','CY','CYP','196','.nc.tr'),('198','Pridnestrovie (Transnistria)','Pridnestrovian Moldavian Republic','Proto Independent State','','','Tiraspol','','Ruple','+373-533','MD','MDA','498','.md'),('199','Somaliland','Republic of Somaliland','Proto Independent State','','','Hargeisa','','Shilling','+252','SO','SOM','706','.so'),('2','Albania','Republic of Albania','Independent State','','','Tirana','ALL','Lek','+355','AL','ALB','008','.al'),('20','Bhutan','Kingdom of Bhutan','Independent State','','','Thimphu','BTN','Ngultrum','+975','BT','BTN','064','.bt'),('200','South Ossetia','Republic of South Ossetia','Proto Independent State','','','Tskhinvali','RUB and GEL','Ruble and Lari','+995','GE','GEO','268','.ge'),('201','Ashmore and Cartier Islands','Territory of Ashmore and Cartier Islands','Dependency','External Territory','Australia','','','','','AU','AUS','036','.au'),('202','Christmas Island','Territory of Christmas Island','Dependency','External Territory','Australia','The Settlement (Flying Fish Cove)','AUD','Dollar','+61','CX','CXR','162','.cx'),('203','Cocos (Keeling) Islands','Territory of Cocos (Keeling) Islands','Dependency','External Territory','Australia','West Island','AUD','Dollar','+61','CC','CCK','166','.cc'),('204','Coral Sea Islands','Coral Sea Islands Territory','Dependency','External Territory','Australia','','','','','AU','AUS','036','.au'),('205','Heard Island and McDonald Islands','Territory of Heard Island and McDonald Islands','Dependency','External Territory','Australia','','','','','HM','HMD','334','.hm'),('206','Norfolk Island','Territory of Norfolk Island','Dependency','External Territory','Australia','Kingston','AUD','Dollar','+672','NF','NFK','574','.nf'),('207','New Caledonia','','Dependency','Sui generis Collectivity','France','Noumea','XPF','Franc','+687','NC','NCL','540','.nc'),('208','French Polynesia','Overseas Country of French Polynesia','Dependency','Overseas Collectivity','France','Papeete','XPF','Franc','+689','PF','PYF','258','.pf'),('209','Mayotte','Departmental Collectivity of Mayotte','Dependency','Overseas Collectivity','France','Mamoudzou','EUR','Euro','+262','YT','MYT','175','.yt'),('21','Bolivia','Republic of Bolivia','Independent State','','','La Paz (administrative/legislative) and Sucre (judical)','BOB','Boliviano','+591','BO','BOL','068','.bo'),('210','Saint Barthelemy','Collectivity of Saint Barthelemy','Dependency','Overseas Collectivity','France','Gustavia','EUR','Euro','+590','GP','GLP','312','.gp'),('211','Saint Martin','Collectivity of Saint Martin','Dependency','Overseas Collectivity','France','Marigot','EUR','Euro','+590','GP','GLP','312','.gp'),('212','Saint Pierre and Miquelon','Territorial Collectivity of Saint Pierre and Miquelon','Dependency','Overseas Collectivity','France','Saint-Pierre','EUR','Euro','+508','PM','SPM','666','.pm'),('213','Wallis and Futuna','Collectivity of the Wallis and Futuna Islands','Dependency','Overseas Collectivity','France','Mata\'utu','XPF','Franc','+681','WF','WLF','876','.wf'),('214','French Southern and Antarctic Lands','Territory of the French Southern and Antarctic Lands','Dependency','Overseas Territory','France','Martin-de-Vivi?s','','','','TF','ATF','260','.tf'),('215','Clipperton Island','','Dependency','Possession','France','','','','','PF','PYF','258','.pf'),('216','Bouvet Island','','Dependency','Territory','Norway','','','','','BV','BVT','074','.bv'),('217','Cook Islands','','Dependency','Self-Governing in Free Association','New Zealand','Avarua','NZD','Dollar','+682','CK','COK','184','.ck'),('218','Niue','','Dependency','Self-Governing in Free Association','New Zealand','Alofi','NZD','Dollar','+683','NU','NIU','570','.nu'),('219','Tokelau','','Dependency','Territory','New Zealand','','NZD','Dollar','+690','TK','TKL','772','.tk'),('22','Bosnia and Herzegovina','','Independent State','','','Sarajevo','BAM','Marka','+387','BA','BIH','070','.ba'),('220','Guernsey','Bailiwick of Guernsey','Dependency','Crown Dependency','United Kingdom','Saint Peter Port','GGP','Pound','+44','GG','GGY','831','.gg'),('221','Isle of Man','','Dependency','Crown Dependency','United Kingdom','Douglas','IMP','Pound','+44','IM','IMN','833','.im'),('222','Jersey','Bailiwick of Jersey','Dependency','Crown Dependency','United Kingdom','Saint Helier','JEP','Pound','+44','JE','JEY','832','.je'),('223','Anguilla','','Dependency','Overseas Territory','United Kingdom','The Valley','XCD','Dollar','+1-264','AI','AIA','660','.ai'),('224','Bermuda','','Dependency','Overseas Territory','United Kingdom','Hamilton','BMD','Dollar','+1-441','BM','BMU','060','.bm'),('225','British Indian Ocean Territory','','Dependency','Overseas Territory','United Kingdom','','','','+246','IO','IOT','086','.io'),('226','British Sovereign Base Areas','','Dependency','Overseas Territory','United Kingdom','Episkopi','CYP','Pound','+357','','','',''),('227','British Virgin Islands','','Dependency','Overseas Territory','United Kingdom','Road Town','USD','Dollar','+1-284','VG','VGB','092','.vg'),('228','Cayman Islands','','Dependency','Overseas Territory','United Kingdom','George Town','KYD','Dollar','+1-345','KY','CYM','136','.ky'),('229','Falkland Islands (Islas Malvinas)','','Dependency','Overseas Territory','United Kingdom','Stanley','FKP','Pound','+500','FK','FLK','238','.fk'),('23','Botswana','Republic of Botswana','Independent State','','','Gaborone','BWP','Pula','+267','BW','BWA','072','.bw'),('230','Gibraltar','','Dependency','Overseas Territory','United Kingdom','Gibraltar','GIP','Pound','+350','GI','GIB','292','.gi'),('231','Montserrat','','Dependency','Overseas Territory','United Kingdom','Plymouth','XCD','Dollar','+1-664','MS','MSR','500','.ms'),('232','Pitcairn Islands','','Dependency','Overseas Territory','United Kingdom','Adamstown','NZD','Dollar','','PN','PCN','612','.pn'),('233','Saint Helena','','Dependency','Overseas Territory','United Kingdom','Jamestown','SHP','Pound','+290','SH','SHN','654','.sh'),('234','South Georgia and the South Sandwich Islands','','Dependency','Overseas Territory','United Kingdom','','','','','GS','SGS','239','.gs'),('235','Turks and Caicos Islands','','Dependency','Overseas Territory','United Kingdom','Grand Turk','USD','Dollar','+1-649','TC','TCA','796','.tc'),('236','Northern Mariana Islands','Commonwealth of The Northern Mariana Islands','Dependency','Commonwealth','United States','Saipan','USD','Dollar','+1-670','MP','MNP','580','.mp'),('237','Puerto Rico','Commonwealth of Puerto Rico','Dependency','Commonwealth','United States','San Juan','USD','Dollar','+1-787 and 1-939','PR','PRI','630','.pr'),('238','American Samoa','Territory of American Samoa','Dependency','Territory','United States','Pago Pago','USD','Dollar','+1-684','AS','ASM','016','.as'),('239','Baker Island','','Dependency','Territory','United States','','','','','UM','UMI','581',''),('24','Brazil','Federative Republic of Brazil','Independent State','','','Brasilia','BRL','Real','+55','BR','BRA','076','.br'),('240','Guam','Territory of Guam','Dependency','Territory','United States','Hagatna','USD','Dollar','+1-671','GU','GUM','316','.gu'),('241','Howland Island','','Dependency','Territory','United States','','','','','UM','UMI','581',''),('242','Jarvis Island','','Dependency','Territory','United States','','','','','UM','UMI','581',''),('243','Johnston Atoll','','Dependency','Territory','United States','','','','','UM','UMI','581',''),('244','Kingman Reef','','Dependency','Territory','United States','','','','','UM','UMI','581',''),('245','Midway Islands','','Dependency','Territory','United States','','','','','UM','UMI','581',''),('246','Navassa Island','','Dependency','Territory','United States','','','','','UM','UMI','581',''),('247','Palmyra Atoll','','Dependency','Territory','United States','','','','','UM','UMI','581',''),('248','U.S. Virgin Islands','United States Virgin Islands','Dependency','Territory','United States','Charlotte Amalie','USD','Dollar','+1-340','VI','VIR','850','.vi'),('249','Wake Island','','Dependency','Territory','United States','','','','','UM','UMI','850',''),('25','Brunei','Negara Brunei Darussalam','Independent State','','','Bandar Seri Begawan','BND','Dollar','+673','BN','BRN','096','.bn'),('250','Hong Kong','Hong Kong Special Administrative Region','Proto Dependency','Special Administrative Region','China','','HKD','Dollar','+852','HK','HKG','344','.hk'),('251','Macau','Macau Special Administrative Region','Proto Dependency','Special Administrative Region','China','Macau','MOP','Pataca','+853','MO','MAC','446','.mo'),('252','Faroe Islands','','Proto Dependency','','Denmark','Torshavn','DKK','Krone','+298','FO','FRO','234','.fo'),('253','Greenland','','Proto Dependency','','Denmark','Nuuk (Godthab)','DKK','Krone','+299','GL','GRL','304','.gl'),('254','French Guiana','Overseas Region of Guiana','Proto Dependency','Overseas Region','France','Cayenne','EUR','Euro','+594','GF','GUF','254','.gf'),('255','Guadeloupe','Overseas Region of Guadeloupe','Proto Dependency','Overseas Region','France','Basse-Terre','EUR','Euro','+590','GP','GLP','312','.gp'),('256','Martinique','Overseas Region of Martinique','Proto Dependency','Overseas Region','France','Fort-de-France','EUR','Euro','+596','MQ','MTQ','474','.mq'),('257','Reunion','Overseas Region of Reunion','Proto Dependency','Overseas Region','France','Saint-Denis','EUR','Euro','+262','RE','REU','638','.re'),('258','Aland','','Proto Dependency','','Finland','Mariehamn','EUR','Euro','+358-18','AX','ALA','248','.ax'),('259','Aruba','','Proto Dependency','','Netherlands','Oranjestad','AWG','Guilder','+297','AW','ABW','533','.aw'),('26','Bulgaria','Republic of Bulgaria','Independent State','','','Sofia','BGN','Lev','+359','BG','BGR','100','.bg'),('260','Netherlands Antilles','','Proto Dependency','','Netherlands','Willemstad','ANG','Guilder','+599','AN','ANT','530','.an'),('261','Svalbard','','Proto Dependency','','Norway','Longyearbyen','NOK','Krone','+47','SJ','SJM','744','.sj'),('262','Ascension','','Proto Dependency','Dependency of Saint Helena','United Kingdom','Georgetown','SHP','Pound','+247','AC','ASC','','.ac'),('263','Tristan da Cunha','','Proto Dependency','Dependency of Saint Helena','United Kingdom','Edinburgh','SHP','Pound','+290','TA','TAA','',''),('264','Antarctica','','Disputed Territory','','Undetermined','','','','','AQ','ATA','010','.aq'),('265','Kosovo','','Disputed Territory','','Administrated by the UN','Pristina','CSD and EUR','Dinar and Euro','+381','CS','SCG','891','.cs and .yu'),('266','Palestinian Territories (Gaza Strip and West Bank)','','Disputed Territory','','Administrated by Israel','Gaza City (Gaza Strip) and Ramallah (West Bank)','ILS','Shekel','+970','PS','PSE','275','.ps'),('267','Western Sahara','','Disputed Territory','','Administrated by Morocco','El-Aaiun','MAD','Dirham','+212','EH','ESH','732','.eh'),('268','Australian Antarctic Territory','','Antarctic Territory','External Territory','Australia','','','','','AQ','ATA','010','.aq'),('269','Ross Dependency','','Antarctic Territory','Territory','New Zealand','','','','','AQ','ATA','010','.aq'),('27','Burkina Faso','','Independent State','','','Ouagadougou','XOF','Franc','+226','BF','BFA','854','.bf'),('270','Peter I Island','','Antarctic Territory','Territory','Norway','','','','','AQ','ATA','010','.aq'),('271','Queen Maud Land','','Antarctic Territory','Territory','Norway','','','','','AQ','ATA','010','.aq'),('272','British Antarctic Territory','','Antarctic Territory','Overseas Territory','United Kingdom','','','','','AQ','ATA','010','.aq'),('28','Burundi','Republic of Burundi','Independent State','','','Bujumbura','BIF','Franc','+257','BI','BDI','108','.bi'),('29','Cambodia','Kingdom of Cambodia','Independent State','','','Phnom Penh','KHR','Riels','+855','KH','KHM','116','.kh'),('3','Algeria','People\'s Democratic Republic of Algeria','Independent State','','','Algiers','DZD','Dinar','+213','DZ','DZA','012','.dz'),('30','Cameroon','Republic of Cameroon','Independent State','','','Yaounde','XAF','Franc','+237','CM','CMR','120','.cm'),('31','Canada','','Independent State','','','Ottawa','CAD','Dollar','+1','CA','CAN','124','.ca'),('32','Cape Verde','Republic of Cape Verde','Independent State','','','Praia','CVE','Escudo','+238','CV','CPV','132','.cv'),('33','Central African Republic','','Independent State','','','Bangui','XAF','Franc','+236','CF','CAF','140','.cf'),('34','Chad','Republic of Chad','Independent State','','','N\'Djamena','XAF','Franc','+235','TD','TCD','148','.td'),('35','Chile','Republic of Chile','Independent State','','','Santiago (administrative/judical) and Valparaiso (legislative)','CLP','Peso','+56','CL','CHL','152','.cl'),('36','China, People\'s Republic of','People\'s Republic of China','Independent State','','','Beijing','CNY','Yuan Renminbi','+86','CN','CHN','156','.cn'),('37','Colombia','Republic of Colombia','Independent State','','','Bogota','COP','Peso','+57','CO','COL','170','.co'),('38','Comoros','Union of Comoros','Independent State','','','Moroni','KMF','Franc','+269','KM','COM','174','.km'),('39','Congo, Democratic Republic of the (Congo ? Kinshasa)','Democratic Republic of the Congo','Independent State','','','Kinshasa','CDF','Franc','+243','CD','COD','180','.cd'),('4','Andorra','Principality of Andorra','Independent State','','','Andorra la Vella','EUR','Euro','+376','AD','AND','020','.ad'),('40','Congo, Republic of the (Congo ? Brazzaville)','Republic of the Congo','Independent State','','','Brazzaville','XAF','Franc','+242','CG','COG','178','.cg'),('41','Costa Rica','Republic of Costa Rica','Independent State','','','San Jose','CRC','Colon','+506','CR','CRI','188','.cr'),('42','Cote d\'Ivoire (Ivory Coast)','Republic of Cote d\'Ivoire','Independent State','','','Yamoussoukro','XOF','Franc','+225','CI','CIV','384','.ci'),('43','Croatia','Republic of Croatia','Independent State','','','Zagreb','HRK','Kuna','+385','HR','HRV','191','.hr'),('44','Cuba','Republic of Cuba','Independent State','','','Havana','CUP','Peso','+53','CU','CUB','192','.cu'),('45','Cyprus','Republic of Cyprus','Independent State','','','Nicosia','CYP','Pound','+357','CY','CYP','196','.cy'),('46','Czech Republic','','Independent State','','','Prague','CZK','Koruna','+420','CZ','CZE','203','.cz'),('47','Denmark','Kingdom of Denmark','Independent State','','','Copenhagen','DKK','Krone','+45','DK','DNK','208','.dk'),('48','Djibouti','Republic of Djibouti','Independent State','','','Djibouti','DJF','Franc','+253','DJ','DJI','262','.dj'),('49','Dominica','Commonwealth of Dominica','Independent State','','','Roseau','XCD','Dollar','+1-767','DM','DMA','212','.dm'),('5','Angola','Republic of Angola','Independent State','','','Luanda','AOA','Kwanza','+244','AO','AGO','024','.ao'),('50','Dominican Republic','','Independent State','','','Santo Domingo','DOP','Peso','+1-809 and 1-829','DO','DOM','214','.do'),('51','Ecuador','Republic of Ecuador','Independent State','','','Quito','USD','Dollar','+593','EC','ECU','218','.ec'),('52','Egypt','Arab Republic of Egypt','Independent State','','','Cairo','EGP','Pound','+20','EG','EGY','818','.eg'),('53','El Salvador','Republic of El Salvador','Independent State','','','San Salvador','USD','Dollar','+503','SV','SLV','222','.sv'),('54','Equatorial Guinea','Republic of Equatorial Guinea','Independent State','','','Malabo','XAF','Franc','+240','GQ','GNQ','226','.gq'),('55','Eritrea','State of Eritrea','Independent State','','','Asmara','ERN','Nakfa','+291','ER','ERI','232','.er'),('56','Estonia','Republic of Estonia','Independent State','','','Tallinn','EEK','Kroon','+372','EE','EST','233','.ee'),('57','Ethiopia','Federal Democratic Republic of Ethiopia','Independent State','','','Addis Ababa','ETB','Birr','+251','ET','ETH','231','.et'),('58','Fiji','Republic of the Fiji Islands','Independent State','','','Suva','FJD','Dollar','+679','FJ','FJI','242','.fj'),('59','Finland','Republic of Finland','Independent State','','','Helsinki','EUR','Euro','+358','FI','FIN','246','.fi'),('6','Antigua and Barbuda','','Independent State','','','Saint John\'s','XCD','Dollar','+1-268','AG','ATG','028','.ag'),('60','France','French Republic','Independent State','','','Paris','EUR','Euro','+33','FR','FRA','250','.fr'),('61','Gabon','Gabonese Republic','Independent State','','','Libreville','XAF','Franc','+241','GA','GAB','266','.ga'),('62','Gambia, The','Republic of The Gambia','Independent State','','','Banjul','GMD','Dalasi','+220','GM','GMB','270','.gm'),('63','Georgia','Republic of Georgia','Independent State','','','Tbilisi','GEL','Lari','+995','GE','GEO','268','.ge'),('64','Germany','Federal Republic of Germany','Independent State','','','Berlin','EUR','Euro','+49','DE','DEU','276','.de'),('65','Ghana','Republic of Ghana','Independent State','','','Accra','GHC','Cedi','+233','GH','GHA','288','.gh'),('66','Greece','Hellenic Republic','Independent State','','','Athens','EUR','Euro','+30','GR','GRC','300','.gr'),('67','Grenada','','Independent State','','','Saint George\'s','XCD','Dollar','+1-473','GD','GRD','308','.gd'),('68','Guatemala','Republic of Guatemala','Independent State','','','Guatemala','GTQ','Quetzal','+502','GT','GTM','320','.gt'),('69','Guinea','Republic of Guinea','Independent State','','','Conakry','GNF','Franc','+224','GN','GIN','324','.gn'),('7','Argentina','Argentine Republic','Independent State','','','Buenos Aires','ARS','Peso','+54','AR','ARG','032','.ar'),('70','Guinea-Bissau','Republic of Guinea-Bissau','Independent State','','','Bissau','XOF','Franc','+245','GW','GNB','624','.gw'),('71','Guyana','Co-operative Republic of Guyana','Independent State','','','Georgetown','GYD','Dollar','+592','GY','GUY','328','.gy'),('72','Haiti','Republic of Haiti','Independent State','','','Port-au-Prince','HTG','Gourde','+509','HT','HTI','332','.ht'),('73','Honduras','Republic of Honduras','Independent State','','','Tegucigalpa','HNL','Lempira','+504','HN','HND','340','.hn'),('74','Hungary','Republic of Hungary','Independent State','','','Budapest','HUF','Forint','+36','HU','HUN','348','.hu'),('75','Iceland','Republic of Iceland','Independent State','','','Reykjavik','ISK','Krona','+354','IS','ISL','352','.is'),('76','India','Republic of India','Independent State','','','New Delhi','INR','Rupee','+91','IN','IND','356','.in'),('77','Indonesia','Republic of Indonesia','Independent State','','','Jakarta','IDR','Rupiah','+62','ID','IDN','360','.id'),('78','Iran','Islamic Republic of Iran','Independent State','','','Tehran','IRR','Rial','+98','IR','IRN','364','.ir'),('79','Iraq','Republic of Iraq','Independent State','','','Baghdad','IQD','Dinar','+964','IQ','IRQ','368','.iq'),('8','Armenia','Republic of Armenia','Independent State','','','Yerevan','AMD','Dram','+374','AM','ARM','051','.am'),('80','Ireland','','Independent State','','','Dublin','EUR','Euro','+353','IE','IRL','372','.ie'),('81','Israel','State of Israel','Independent State','','','Jerusalem','ILS','Shekel','+972','IL','ISR','376','.il'),('82','Italy','Italian Republic','Independent State','','','Rome','EUR','Euro','+39','IT','ITA','380','.it'),('83','Jamaica','','Independent State','','','Kingston','JMD','Dollar','+1-876','JM','JAM','388','.jm'),('84','Japan','','Independent State','','','Tokyo','JPY','Yen','+81','JP','JPN','392','.jp'),('85','Jordan','Hashemite Kingdom of Jordan','Independent State','','','Amman','JOD','Dinar','+962','JO','JOR','400','.jo'),('86','Kazakhstan','Republic of Kazakhstan','Independent State','','','Astana','KZT','Tenge','+7','KZ','KAZ','398','.kz'),('87','Kenya','Republic of Kenya','Independent State','','','Nairobi','KES','Shilling','+254','KE','KEN','404','.ke'),('88','Kiribati','Republic of Kiribati','Independent State','','','Tarawa','AUD','Dollar','+686','KI','KIR','296','.ki'),('89','Korea, Democratic People\'s Republic of (North Korea)','Democratic People\'s Republic of Korea','Independent State','','','Pyongyang','KPW','Won','+850','KP','PRK','408','.kp'),('9','Australia','Commonwealth of Australia','Independent State','','','Canberra','AUD','Dollar','+61','AU','AUS','036','.au'),('90','Korea, Republic of  (South Korea)','Republic of Korea','Independent State','','','Seoul','KRW','Won','+82','KR','KOR','410','.kr'),('91','Kuwait','State of Kuwait','Independent State','','','Kuwait','KWD','Dinar','+965','KW','KWT','414','.kw'),('92','Kyrgyzstan','Kyrgyz Republic','Independent State','','','Bishkek','KGS','Som','+996','KG','KGZ','417','.kg'),('93','Laos','Lao People\'s Democratic Republic','Independent State','','','Vientiane','LAK','Kip','+856','LA','LAO','418','.la'),('94','Latvia','Republic of Latvia','Independent State','','','Riga','LVL','Lat','+371','LV','LVA','428','.lv'),('95','Lebanon','Lebanese Republic','Independent State','','','Beirut','LBP','Pound','+961','LB','LBN','422','.lb'),('96','Lesotho','Kingdom of Lesotho','Independent State','','','Maseru','LSL','Loti','+266','LS','LSO','426','.ls'),('97','Liberia','Republic of Liberia','Independent State','','','Monrovia','LRD','Dollar','+231','LR','LBR','430','.lr'),('98','Libya','Great Socialist People\'s Libyan Arab Jamahiriya','Independent State','','','Tripoli','LYD','Dinar','+218','LY','LBY','434','.ly'),('99','Liechtenstein','Principality of Liechtenstein','Independent State','','','Vaduz','CHF','Franc','+423','LI','LIE','438','.li');
/*!40000 ALTER TABLE `rprj_countrylist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_dbversion`
--

DROP TABLE IF EXISTS `rprj_dbversion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_dbversion` (
  `model_name` varchar(100) NOT NULL,
  `version` int(11) NOT NULL,
  PRIMARY KEY (`model_name`),
  KEY `rprj_dbversion_0` (`model_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_dbversion`
--

LOCK TABLES `rprj_dbversion` WRITE;
/*!40000 ALTER TABLE `rprj_dbversion` DISABLE KEYS */;
INSERT INTO `rprj_dbversion` VALUES ('rprj',2);
/*!40000 ALTER TABLE `rprj_dbversion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_events`
--

DROP TABLE IF EXISTS `rprj_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_events` (
  `id` varchar(16) NOT NULL,
  `owner` varchar(16) NOT NULL,
  `group_id` varchar(16) NOT NULL,
  `permissions` char(9) NOT NULL DEFAULT 'rwx------',
  `creator` varchar(16) NOT NULL,
  `creation_date` datetime DEFAULT NULL,
  `last_modify` varchar(16) NOT NULL,
  `last_modify_date` datetime DEFAULT NULL,
  `deleted_by` varchar(16) DEFAULT NULL,
  `deleted_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `father_id` varchar(16) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `fk_obj_id` varchar(16) DEFAULT NULL,
  `start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `all_day` char(1) NOT NULL DEFAULT '1',
  `url` varchar(255) DEFAULT NULL,
  `alarm` char(1) DEFAULT '0',
  `alarm_minute` int(11) DEFAULT '0',
  `alarm_unit` char(1) DEFAULT '0',
  `before_event` char(1) DEFAULT '0',
  `category` varchar(255) DEFAULT '',
  `recurrence` char(1) DEFAULT '0',
  `recurrence_type` char(1) DEFAULT '0',
  `daily_every_x` int(11) DEFAULT '0',
  `weekly_every_x` int(11) DEFAULT '0',
  `weekly_day_of_the_week` char(1) DEFAULT '0',
  `monthly_every_x` int(11) DEFAULT '0',
  `monthly_day_of_the_month` int(11) DEFAULT '0',
  `monthly_week_number` int(11) DEFAULT '0',
  `monthly_week_day` char(1) DEFAULT '0',
  `yearly_month_number` int(11) DEFAULT '0',
  `yearly_month_day` int(11) DEFAULT '0',
  `yearly_week_number` int(11) DEFAULT '0',
  `yearly_week_day` char(1) DEFAULT '0',
  `yearly_day_of_the_year` int(11) DEFAULT '0',
  `recurrence_times` int(11) DEFAULT '0',
  `recurrence_end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `rprj_events_0` (`id`),
  KEY `rprj_events_1` (`owner`),
  KEY `rprj_events_2` (`group_id`),
  KEY `rprj_events_3` (`creator`),
  KEY `rprj_events_4` (`last_modify`),
  KEY `rprj_events_5` (`deleted_by`),
  KEY `rprj_events_6` (`father_id`),
  KEY `rprj_events_7` (`fk_obj_id`),
  KEY `rprj_events_8` (`fk_obj_id`),
  KEY `rprj_events_9` (`fk_obj_id`),
  KEY `rprj_events_10` (`fk_obj_id`),
  KEY `rprj_events_idx2` (`start_date`),
  KEY `rprj_events_idx3` (`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_events`
--

LOCK TABLES `rprj_events` WRITE;
/*!40000 ALTER TABLE `rprj_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `rprj_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_files`
--

DROP TABLE IF EXISTS `rprj_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_files` (
  `id` varchar(16) NOT NULL,
  `owner` varchar(16) NOT NULL,
  `group_id` varchar(16) NOT NULL,
  `permissions` char(9) NOT NULL DEFAULT 'rwx------',
  `creator` varchar(16) NOT NULL,
  `creation_date` datetime DEFAULT NULL,
  `last_modify` varchar(16) NOT NULL,
  `last_modify_date` datetime DEFAULT NULL,
  `deleted_by` varchar(16) DEFAULT NULL,
  `deleted_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `father_id` varchar(16) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `fk_obj_id` varchar(16) DEFAULT NULL,
  `path` text,
  `filename` text NOT NULL,
  `checksum` char(40) DEFAULT NULL,
  `mime` varchar(255) DEFAULT NULL,
  `alt_link` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `rprj_files_0` (`id`),
  KEY `rprj_files_1` (`owner`),
  KEY `rprj_files_2` (`group_id`),
  KEY `rprj_files_3` (`creator`),
  KEY `rprj_files_4` (`last_modify`),
  KEY `rprj_files_5` (`deleted_by`),
  KEY `rprj_files_6` (`father_id`),
  KEY `rprj_files_7` (`father_id`),
  KEY `rprj_files_8` (`fk_obj_id`),
  KEY `rprj_files_9` (`father_id`),
  KEY `rprj_files_10` (`fk_obj_id`),
  KEY `rprj_files_11` (`father_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_files`
--

LOCK TABLES `rprj_files` WRITE;
/*!40000 ALTER TABLE `rprj_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `rprj_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_folders`
--

DROP TABLE IF EXISTS `rprj_folders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_folders` (
  `id` varchar(16) NOT NULL,
  `owner` varchar(16) NOT NULL,
  `group_id` varchar(16) NOT NULL,
  `permissions` char(9) NOT NULL DEFAULT 'rwx------',
  `creator` varchar(16) NOT NULL,
  `creation_date` datetime DEFAULT NULL,
  `last_modify` varchar(16) NOT NULL,
  `last_modify_date` datetime DEFAULT NULL,
  `deleted_by` varchar(16) DEFAULT NULL,
  `deleted_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `father_id` varchar(16) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `fk_obj_id` varchar(16) DEFAULT NULL,
  `childs_sort_order` text,
  PRIMARY KEY (`id`),
  KEY `rprj_folders_0` (`id`),
  KEY `rprj_folders_1` (`owner`),
  KEY `rprj_folders_2` (`group_id`),
  KEY `rprj_folders_3` (`creator`),
  KEY `rprj_folders_4` (`last_modify`),
  KEY `rprj_folders_5` (`deleted_by`),
  KEY `rprj_folders_6` (`father_id`),
  KEY `rprj_folders_7` (`fk_obj_id`),
  KEY `rprj_folders_8` (`fk_obj_id`),
  KEY `rprj_folders_9` (`fk_obj_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_folders`
--

LOCK TABLES `rprj_folders` WRITE;
/*!40000 ALTER TABLE `rprj_folders` DISABLE KEYS */;
INSERT INTO `rprj_folders` VALUES ('-10','-1','-6','rwxrw-r--','-1','2020-02-05 13:42:04','-1','2020-02-05 13:42:04',NULL,'0000-00-00 00:00:00','0','Home','','0','-11,-12,-13,-14'),('-11','-1','-6','rwxrw-r--','-1','2020-02-05 13:42:04','-1','2020-02-05 13:42:04',NULL,'0000-00-00 00:00:00','-10','Products','','0',''),('-12','-1','-6','rwxrw-r--','-1','2020-02-05 13:42:04','-1','2020-02-05 13:42:04',NULL,'0000-00-00 00:00:00','-10','Services','','0',''),('-13','-1','-6','rwxrw-r--','-1','2020-02-05 13:42:04','-1','2020-02-05 13:42:04',NULL,'0000-00-00 00:00:00','-10','Downloads','','0',''),('-14','-1','-6','rwxrw-r--','-1','2020-02-05 13:42:04','-1','2020-02-05 13:42:04',NULL,'0000-00-00 00:00:00','-10','About us','','0','');
/*!40000 ALTER TABLE `rprj_folders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_groups`
--

DROP TABLE IF EXISTS `rprj_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_groups` (
  `id` varchar(16) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  KEY `rprj_groups_0` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_groups`
--

LOCK TABLES `rprj_groups` WRITE;
/*!40000 ALTER TABLE `rprj_groups` DISABLE KEYS */;
INSERT INTO `rprj_groups` VALUES ('-2','Admin','System admins'),('-3','Users','System users'),('-4','Guests','System guests (read only)'),('-5','Project','R-Project user'),('-6','Webmaster','Web content creators');
/*!40000 ALTER TABLE `rprj_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_links`
--

DROP TABLE IF EXISTS `rprj_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_links` (
  `id` varchar(16) NOT NULL,
  `owner` varchar(16) NOT NULL,
  `group_id` varchar(16) NOT NULL,
  `permissions` char(9) NOT NULL DEFAULT 'rwx------',
  `creator` varchar(16) NOT NULL,
  `creation_date` datetime DEFAULT NULL,
  `last_modify` varchar(16) NOT NULL,
  `last_modify_date` datetime DEFAULT NULL,
  `deleted_by` varchar(16) DEFAULT NULL,
  `deleted_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `father_id` varchar(16) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `href` varchar(255) NOT NULL,
  `target` varchar(255) DEFAULT '_blank',
  `fk_obj_id` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rprj_links_0` (`id`),
  KEY `rprj_links_1` (`owner`),
  KEY `rprj_links_2` (`group_id`),
  KEY `rprj_links_3` (`creator`),
  KEY `rprj_links_4` (`last_modify`),
  KEY `rprj_links_5` (`deleted_by`),
  KEY `rprj_links_6` (`father_id`),
  KEY `rprj_links_7` (`fk_obj_id`),
  KEY `rprj_links_8` (`fk_obj_id`),
  KEY `rprj_links_9` (`fk_obj_id`),
  KEY `rprj_links_10` (`fk_obj_id`),
  KEY `rprj_links_11` (`fk_obj_id`),
  KEY `rprj_links_12` (`father_id`),
  KEY `rprj_links_13` (`fk_obj_id`),
  KEY `rprj_links_14` (`father_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_links`
--

LOCK TABLES `rprj_links` WRITE;
/*!40000 ALTER TABLE `rprj_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `rprj_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_log`
--

DROP TABLE IF EXISTS `rprj_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_log` (
  `ip` varchar(16) NOT NULL,
  `data` date NOT NULL DEFAULT '0000-00-00',
  `ora` time NOT NULL DEFAULT '00:00:00',
  `count` int(11) NOT NULL DEFAULT '0',
  `url` varchar(255) DEFAULT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  `note2` text NOT NULL,
  PRIMARY KEY (`ip`,`data`),
  KEY `rprj_log_0` (`ip`),
  KEY `rprj_log_1` (`data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_log`
--

LOCK TABLES `rprj_log` WRITE;
/*!40000 ALTER TABLE `rprj_log` DISABLE KEYS */;
INSERT INTO `rprj_log` VALUES ('192.168.197.58','2020-02-05','15:45:57',38,NULL,'','14:42:08-/main.php\n14:44:09-/main.php\n14:46:09-/main.php\n14:48:10-/main.php\n14:50:11-/main.php\n14:50:44-/main.php?obj_id=-14\n14:50:46-/main.php?obj_id=-13\n14:50:46-/main.php?obj_id=-12\n14:50:47-/main.php?obj_id=-11\n14:50:49-/main.php?obj_id=-10\n14:52:49-/main.php?obj_id=-10\n14:54:50-/main.php?obj_id=-10\n14:56:51-/main.php?obj_id=-10\n14:57:36-/main.php\n14:59:36-/main.php\n15:01:37-/main.php\n15:03:38-/main.php\n15:05:39-/main.php\n15:07:40-/main.php\n15:09:40-/main.php\n15:11:41-/main.php\n15:13:42-/main.php\n15:15:43-/main.php\n15:17:44-/main.php\n15:19:45-/main.php\n15:21:46-/main.php\n15:23:47-/main.php\n15:25:48-/main.php\n15:27:49-/main.php\n15:29:50-/main.php\n15:31:50-/main.php\n15:33:51-/main.php\n15:35:52-/main.php\n15:37:53-/main.php\n15:39:54-/main.php\n15:41:55-/main.php\n15:43:56-/main.php\n15:45:57-/main.php');
/*!40000 ALTER TABLE `rprj_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_news`
--

DROP TABLE IF EXISTS `rprj_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_news` (
  `id` varchar(16) NOT NULL,
  `owner` varchar(16) NOT NULL,
  `group_id` varchar(16) NOT NULL,
  `permissions` char(9) NOT NULL DEFAULT 'rwx------',
  `creator` varchar(16) NOT NULL,
  `creation_date` datetime DEFAULT NULL,
  `last_modify` varchar(16) NOT NULL,
  `last_modify_date` datetime DEFAULT NULL,
  `deleted_by` varchar(16) DEFAULT NULL,
  `deleted_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `father_id` varchar(16) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `html` text,
  `fk_obj_id` varchar(16) DEFAULT NULL,
  `language` varchar(5) DEFAULT 'en_us',
  PRIMARY KEY (`id`),
  KEY `rprj_news_0` (`id`),
  KEY `rprj_news_1` (`owner`),
  KEY `rprj_news_2` (`group_id`),
  KEY `rprj_news_3` (`creator`),
  KEY `rprj_news_4` (`last_modify`),
  KEY `rprj_news_5` (`deleted_by`),
  KEY `rprj_news_6` (`father_id`),
  KEY `rprj_news_7` (`fk_obj_id`),
  KEY `rprj_news_8` (`fk_obj_id`),
  KEY `rprj_news_9` (`fk_obj_id`),
  KEY `rprj_news_10` (`fk_obj_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_news`
--

LOCK TABLES `rprj_news` WRITE;
/*!40000 ALTER TABLE `rprj_news` DISABLE KEYS */;
/*!40000 ALTER TABLE `rprj_news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_notes`
--

DROP TABLE IF EXISTS `rprj_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_notes` (
  `id` varchar(16) NOT NULL,
  `owner` varchar(16) NOT NULL,
  `group_id` varchar(16) NOT NULL,
  `permissions` char(9) NOT NULL DEFAULT 'rwx------',
  `creator` varchar(16) NOT NULL,
  `creation_date` datetime DEFAULT NULL,
  `last_modify` varchar(16) NOT NULL,
  `last_modify_date` datetime DEFAULT NULL,
  `deleted_by` varchar(16) DEFAULT NULL,
  `deleted_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `father_id` varchar(16) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `fk_obj_id` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rprj_notes_0` (`id`),
  KEY `rprj_notes_1` (`owner`),
  KEY `rprj_notes_2` (`group_id`),
  KEY `rprj_notes_3` (`creator`),
  KEY `rprj_notes_4` (`last_modify`),
  KEY `rprj_notes_5` (`deleted_by`),
  KEY `rprj_notes_6` (`father_id`),
  KEY `rprj_notes_7` (`fk_obj_id`),
  KEY `rprj_notes_8` (`fk_obj_id`),
  KEY `rprj_notes_9` (`fk_obj_id`),
  KEY `rprj_notes_10` (`fk_obj_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_notes`
--

LOCK TABLES `rprj_notes` WRITE;
/*!40000 ALTER TABLE `rprj_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `rprj_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_objects`
--

DROP TABLE IF EXISTS `rprj_objects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_objects` (
  `id` varchar(16) NOT NULL,
  `owner` varchar(16) NOT NULL,
  `group_id` varchar(16) NOT NULL,
  `permissions` char(9) NOT NULL DEFAULT 'rwx------',
  `creator` varchar(16) NOT NULL,
  `creation_date` datetime DEFAULT NULL,
  `last_modify` varchar(16) NOT NULL,
  `last_modify_date` datetime DEFAULT NULL,
  `deleted_by` varchar(16) DEFAULT NULL,
  `deleted_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `father_id` varchar(16) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  KEY `rprj_objects_0` (`id`),
  KEY `rprj_objects_1` (`owner`),
  KEY `rprj_objects_2` (`group_id`),
  KEY `rprj_objects_3` (`creator`),
  KEY `rprj_objects_4` (`last_modify`),
  KEY `rprj_objects_5` (`deleted_by`),
  KEY `rprj_objects_6` (`father_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_objects`
--

LOCK TABLES `rprj_objects` WRITE;
/*!40000 ALTER TABLE `rprj_objects` DISABLE KEYS */;
/*!40000 ALTER TABLE `rprj_objects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_pages`
--

DROP TABLE IF EXISTS `rprj_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_pages` (
  `id` varchar(16) NOT NULL,
  `owner` varchar(16) NOT NULL,
  `group_id` varchar(16) NOT NULL,
  `permissions` char(9) NOT NULL DEFAULT 'rwx------',
  `creator` varchar(16) NOT NULL,
  `creation_date` datetime DEFAULT NULL,
  `last_modify` varchar(16) NOT NULL,
  `last_modify_date` datetime DEFAULT NULL,
  `deleted_by` varchar(16) DEFAULT NULL,
  `deleted_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `father_id` varchar(16) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `html` text,
  `fk_obj_id` varchar(16) DEFAULT NULL,
  `language` varchar(5) DEFAULT 'en_us',
  PRIMARY KEY (`id`),
  KEY `rprj_pages_0` (`id`),
  KEY `rprj_pages_1` (`owner`),
  KEY `rprj_pages_2` (`group_id`),
  KEY `rprj_pages_3` (`creator`),
  KEY `rprj_pages_4` (`last_modify`),
  KEY `rprj_pages_5` (`deleted_by`),
  KEY `rprj_pages_6` (`father_id`),
  KEY `rprj_pages_7` (`fk_obj_id`),
  KEY `rprj_pages_8` (`fk_obj_id`),
  KEY `rprj_pages_9` (`fk_obj_id`),
  KEY `rprj_pages_10` (`fk_obj_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_pages`
--

LOCK TABLES `rprj_pages` WRITE;
/*!40000 ALTER TABLE `rprj_pages` DISABLE KEYS */;
INSERT INTO `rprj_pages` VALUES ('-20','-1','-6','rwxrw-r--','-1','2020-02-05 13:42:04','-1','2020-02-05 13:42:04',NULL,'0000-00-00 00:00:00','-10','index','','<div id=\"underconstruction\"><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/></div>','-10','en_us'),('-21','-1','-6','rwxrw-r--','-1','2020-02-05 13:42:04','-1','2020-02-05 13:42:04',NULL,'0000-00-00 00:00:00','-11','index','','<div id=\"underconstruction\"><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/></div>','-11','en_us'),('-22','-1','-6','rwxrw-r--','-1','2020-02-05 13:42:04','-1','2020-02-05 13:42:04',NULL,'0000-00-00 00:00:00','-12','index','','<div id=\"underconstruction\"><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/></div>','-12','en_us'),('-23','-1','-6','rwxrw-r--','-1','2020-02-05 13:42:04','-1','2020-02-05 13:42:04',NULL,'0000-00-00 00:00:00','-13','index','','<div id=\"underconstruction\"><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/></div>','-13','en_us'),('-24','-1','-6','rwxrw-r--','-1','2020-02-05 13:42:04','-1','2020-02-05 13:42:04',NULL,'0000-00-00 00:00:00','-14','index','','<div id=\"underconstruction\"><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/></div>','-14','en_us');
/*!40000 ALTER TABLE `rprj_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_people`
--

DROP TABLE IF EXISTS `rprj_people`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_people` (
  `id` varchar(16) NOT NULL,
  `owner` varchar(16) NOT NULL,
  `group_id` varchar(16) NOT NULL,
  `permissions` char(9) NOT NULL DEFAULT 'rwx------',
  `creator` varchar(16) NOT NULL,
  `creation_date` datetime DEFAULT NULL,
  `last_modify` varchar(16) NOT NULL,
  `last_modify_date` datetime DEFAULT NULL,
  `deleted_by` varchar(16) DEFAULT NULL,
  `deleted_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `father_id` varchar(16) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `street` varchar(255) DEFAULT NULL,
  `zip` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `fk_countrylist_id` varchar(16) DEFAULT NULL,
  `fk_companies_id` varchar(16) DEFAULT NULL,
  `fk_users_id` varchar(16) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `office_phone` varchar(255) DEFAULT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `fax` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `codice_fiscale` varchar(20) DEFAULT NULL,
  `p_iva` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rprj_people_0` (`id`),
  KEY `rprj_people_1` (`owner`),
  KEY `rprj_people_2` (`group_id`),
  KEY `rprj_people_3` (`creator`),
  KEY `rprj_people_4` (`last_modify`),
  KEY `rprj_people_5` (`deleted_by`),
  KEY `rprj_people_6` (`father_id`),
  KEY `rprj_people_7` (`fk_countrylist_id`),
  KEY `rprj_people_8` (`fk_companies_id`),
  KEY `rprj_people_9` (`fk_users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_people`
--

LOCK TABLES `rprj_people` WRITE;
/*!40000 ALTER TABLE `rprj_people` DISABLE KEYS */;
/*!40000 ALTER TABLE `rprj_people` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_projects`
--

DROP TABLE IF EXISTS `rprj_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_projects` (
  `id` varchar(16) NOT NULL,
  `owner` varchar(16) NOT NULL,
  `group_id` varchar(16) NOT NULL,
  `permissions` char(9) NOT NULL DEFAULT 'rwx------',
  `creator` varchar(16) NOT NULL,
  `creation_date` datetime DEFAULT NULL,
  `last_modify` varchar(16) NOT NULL,
  `last_modify_date` datetime DEFAULT NULL,
  `deleted_by` varchar(16) DEFAULT NULL,
  `deleted_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `father_id` varchar(16) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  KEY `rprj_projects_0` (`id`),
  KEY `rprj_projects_1` (`owner`),
  KEY `rprj_projects_2` (`group_id`),
  KEY `rprj_projects_3` (`creator`),
  KEY `rprj_projects_4` (`last_modify`),
  KEY `rprj_projects_5` (`deleted_by`),
  KEY `rprj_projects_6` (`father_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_projects`
--

LOCK TABLES `rprj_projects` WRITE;
/*!40000 ALTER TABLE `rprj_projects` DISABLE KEYS */;
/*!40000 ALTER TABLE `rprj_projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_projects_companies`
--

DROP TABLE IF EXISTS `rprj_projects_companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_projects_companies` (
  `project_id` varchar(16) NOT NULL DEFAULT '',
  `company_id` varchar(16) NOT NULL DEFAULT '',
  `projects_companies_role_id` varchar(16) NOT NULL DEFAULT '',
  PRIMARY KEY (`project_id`,`company_id`,`projects_companies_role_id`),
  KEY `rprj_projects_companies_0` (`project_id`),
  KEY `rprj_projects_companies_1` (`company_id`),
  KEY `rprj_projects_companies_2` (`projects_companies_role_id`),
  KEY `rprj_projects_companies_3` (`project_id`),
  KEY `rprj_projects_companies_4` (`company_id`),
  KEY `rprj_projects_companies_5` (`projects_companies_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_projects_companies`
--

LOCK TABLES `rprj_projects_companies` WRITE;
/*!40000 ALTER TABLE `rprj_projects_companies` DISABLE KEYS */;
/*!40000 ALTER TABLE `rprj_projects_companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_projects_companies_roles`
--

DROP TABLE IF EXISTS `rprj_projects_companies_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_projects_companies_roles` (
  `id` varchar(16) NOT NULL,
  `owner` varchar(16) NOT NULL,
  `group_id` varchar(16) NOT NULL,
  `permissions` char(9) NOT NULL DEFAULT 'rwx------',
  `creator` varchar(16) NOT NULL,
  `creation_date` datetime DEFAULT NULL,
  `last_modify` varchar(16) NOT NULL,
  `last_modify_date` datetime DEFAULT NULL,
  `deleted_by` varchar(16) DEFAULT NULL,
  `deleted_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `father_id` varchar(16) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `order_position` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `rprj_projects_companies_roles_0` (`id`),
  KEY `rprj_projects_companies_roles_1` (`owner`),
  KEY `rprj_projects_companies_roles_2` (`group_id`),
  KEY `rprj_projects_companies_roles_3` (`creator`),
  KEY `rprj_projects_companies_roles_4` (`last_modify`),
  KEY `rprj_projects_companies_roles_5` (`deleted_by`),
  KEY `rprj_projects_companies_roles_6` (`father_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_projects_companies_roles`
--

LOCK TABLES `rprj_projects_companies_roles` WRITE;
/*!40000 ALTER TABLE `rprj_projects_companies_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `rprj_projects_companies_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_projects_people`
--

DROP TABLE IF EXISTS `rprj_projects_people`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_projects_people` (
  `project_id` varchar(16) NOT NULL DEFAULT '',
  `people_id` varchar(16) NOT NULL DEFAULT '',
  `projects_people_role_id` varchar(16) NOT NULL DEFAULT '',
  PRIMARY KEY (`project_id`,`people_id`,`projects_people_role_id`),
  KEY `rprj_projects_people_0` (`project_id`),
  KEY `rprj_projects_people_1` (`people_id`),
  KEY `rprj_projects_people_2` (`projects_people_role_id`),
  KEY `rprj_projects_people_3` (`project_id`),
  KEY `rprj_projects_people_4` (`people_id`),
  KEY `rprj_projects_people_5` (`projects_people_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_projects_people`
--

LOCK TABLES `rprj_projects_people` WRITE;
/*!40000 ALTER TABLE `rprj_projects_people` DISABLE KEYS */;
/*!40000 ALTER TABLE `rprj_projects_people` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_projects_people_roles`
--

DROP TABLE IF EXISTS `rprj_projects_people_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_projects_people_roles` (
  `id` varchar(16) NOT NULL,
  `owner` varchar(16) NOT NULL,
  `group_id` varchar(16) NOT NULL,
  `permissions` char(9) NOT NULL DEFAULT 'rwx------',
  `creator` varchar(16) NOT NULL,
  `creation_date` datetime DEFAULT NULL,
  `last_modify` varchar(16) NOT NULL,
  `last_modify_date` datetime DEFAULT NULL,
  `deleted_by` varchar(16) DEFAULT NULL,
  `deleted_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `father_id` varchar(16) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `order_position` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `rprj_projects_people_roles_0` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_projects_people_roles`
--

LOCK TABLES `rprj_projects_people_roles` WRITE;
/*!40000 ALTER TABLE `rprj_projects_people_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `rprj_projects_people_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_projects_projects`
--

DROP TABLE IF EXISTS `rprj_projects_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_projects_projects` (
  `project_id` varchar(16) NOT NULL DEFAULT '',
  `project2_id` varchar(16) NOT NULL DEFAULT '',
  `projects_projects_role_id` varchar(16) NOT NULL DEFAULT '',
  PRIMARY KEY (`project_id`,`project2_id`,`projects_projects_role_id`),
  KEY `rprj_projects_projects_0` (`project_id`),
  KEY `rprj_projects_projects_1` (`project2_id`),
  KEY `rprj_projects_projects_2` (`projects_projects_role_id`),
  KEY `rprj_projects_projects_3` (`project_id`),
  KEY `rprj_projects_projects_4` (`project2_id`),
  KEY `rprj_projects_projects_5` (`projects_projects_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_projects_projects`
--

LOCK TABLES `rprj_projects_projects` WRITE;
/*!40000 ALTER TABLE `rprj_projects_projects` DISABLE KEYS */;
/*!40000 ALTER TABLE `rprj_projects_projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_projects_projects_roles`
--

DROP TABLE IF EXISTS `rprj_projects_projects_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_projects_projects_roles` (
  `id` varchar(16) NOT NULL,
  `owner` varchar(16) NOT NULL,
  `group_id` varchar(16) NOT NULL,
  `permissions` char(9) NOT NULL DEFAULT 'rwx------',
  `creator` varchar(16) NOT NULL,
  `creation_date` datetime DEFAULT NULL,
  `last_modify` varchar(16) NOT NULL,
  `last_modify_date` datetime DEFAULT NULL,
  `deleted_by` varchar(16) DEFAULT NULL,
  `deleted_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `father_id` varchar(16) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `order_position` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `rprj_projects_projects_roles_0` (`id`),
  KEY `rprj_projects_projects_roles_1` (`owner`),
  KEY `rprj_projects_projects_roles_2` (`group_id`),
  KEY `rprj_projects_projects_roles_3` (`creator`),
  KEY `rprj_projects_projects_roles_4` (`last_modify`),
  KEY `rprj_projects_projects_roles_5` (`deleted_by`),
  KEY `rprj_projects_projects_roles_6` (`father_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_projects_projects_roles`
--

LOCK TABLES `rprj_projects_projects_roles` WRITE;
/*!40000 ALTER TABLE `rprj_projects_projects_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `rprj_projects_projects_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_timetracks`
--

DROP TABLE IF EXISTS `rprj_timetracks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_timetracks` (
  `id` varchar(16) NOT NULL,
  `owner` varchar(16) NOT NULL,
  `group_id` varchar(16) NOT NULL,
  `permissions` char(9) NOT NULL DEFAULT 'rwx------',
  `creator` varchar(16) NOT NULL,
  `creation_date` datetime DEFAULT NULL,
  `last_modify` varchar(16) NOT NULL,
  `last_modify_date` datetime DEFAULT NULL,
  `deleted_by` varchar(16) DEFAULT NULL,
  `deleted_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `father_id` varchar(16) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `fk_obj_id` varchar(16) DEFAULT NULL,
  `fk_progetto` varchar(16) DEFAULT NULL,
  `dalle_ore` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `alle_ore` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ore_intervento` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ore_viaggio` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `km_viaggio` int(11) NOT NULL DEFAULT '0',
  `luogo_di_intervento` int(11) NOT NULL DEFAULT '0',
  `stato` int(11) NOT NULL DEFAULT '0',
  `costo_per_ora` float NOT NULL DEFAULT '0',
  `costo_valuta` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rprj_timetracks_0` (`id`),
  KEY `rprj_timetracks_1` (`owner`),
  KEY `rprj_timetracks_2` (`group_id`),
  KEY `rprj_timetracks_3` (`creator`),
  KEY `rprj_timetracks_4` (`last_modify`),
  KEY `rprj_timetracks_5` (`deleted_by`),
  KEY `rprj_timetracks_6` (`father_id`),
  KEY `rprj_timetracks_7` (`fk_obj_id`),
  KEY `rprj_timetracks_8` (`fk_obj_id`),
  KEY `rprj_timetracks_9` (`fk_obj_id`),
  KEY `rprj_timetracks_10` (`fk_progetto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_timetracks`
--

LOCK TABLES `rprj_timetracks` WRITE;
/*!40000 ALTER TABLE `rprj_timetracks` DISABLE KEYS */;
/*!40000 ALTER TABLE `rprj_timetracks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_todo`
--

DROP TABLE IF EXISTS `rprj_todo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_todo` (
  `id` varchar(16) NOT NULL,
  `owner` varchar(16) NOT NULL,
  `group_id` varchar(16) NOT NULL,
  `permissions` char(9) NOT NULL DEFAULT 'rwx------',
  `creator` varchar(16) NOT NULL,
  `creation_date` datetime DEFAULT NULL,
  `last_modify` varchar(16) NOT NULL,
  `last_modify_date` datetime DEFAULT NULL,
  `deleted_by` varchar(16) DEFAULT NULL,
  `deleted_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `father_id` varchar(16) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `priority` int(11) NOT NULL DEFAULT '0',
  `data_segnalazione` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `fk_segnalato_da` varchar(16) DEFAULT NULL,
  `fk_cliente` varchar(16) DEFAULT NULL,
  `fk_progetto` varchar(16) DEFAULT NULL,
  `fk_funzionalita` varchar(16) DEFAULT NULL,
  `fk_tipo` varchar(16) DEFAULT NULL,
  `stato` int(11) NOT NULL DEFAULT '0',
  `descrizione` text NOT NULL,
  `intervento` text NOT NULL,
  `data_chiusura` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `rprj_todo_0` (`id`),
  KEY `rprj_todo_1` (`owner`),
  KEY `rprj_todo_2` (`group_id`),
  KEY `rprj_todo_3` (`creator`),
  KEY `rprj_todo_4` (`last_modify`),
  KEY `rprj_todo_5` (`deleted_by`),
  KEY `rprj_todo_6` (`father_id`),
  KEY `rprj_todo_7` (`fk_segnalato_da`),
  KEY `rprj_todo_8` (`fk_cliente`),
  KEY `rprj_todo_9` (`fk_progetto`),
  KEY `rprj_todo_10` (`father_id`),
  KEY `rprj_todo_11` (`father_id`),
  KEY `rprj_todo_12` (`fk_tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_todo`
--

LOCK TABLES `rprj_todo` WRITE;
/*!40000 ALTER TABLE `rprj_todo` DISABLE KEYS */;
/*!40000 ALTER TABLE `rprj_todo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_todo_tipo`
--

DROP TABLE IF EXISTS `rprj_todo_tipo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_todo_tipo` (
  `id` varchar(16) NOT NULL,
  `owner` varchar(16) NOT NULL,
  `group_id` varchar(16) NOT NULL,
  `permissions` char(9) NOT NULL DEFAULT 'rwx------',
  `creator` varchar(16) NOT NULL,
  `creation_date` datetime DEFAULT NULL,
  `last_modify` varchar(16) NOT NULL,
  `last_modify_date` datetime DEFAULT NULL,
  `deleted_by` varchar(16) DEFAULT NULL,
  `deleted_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `father_id` varchar(16) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `order_position` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `rprj_todo_tipo_0` (`id`),
  KEY `rprj_todo_tipo_1` (`owner`),
  KEY `rprj_todo_tipo_2` (`group_id`),
  KEY `rprj_todo_tipo_3` (`creator`),
  KEY `rprj_todo_tipo_4` (`last_modify`),
  KEY `rprj_todo_tipo_5` (`deleted_by`),
  KEY `rprj_todo_tipo_6` (`father_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_todo_tipo`
--

LOCK TABLES `rprj_todo_tipo` WRITE;
/*!40000 ALTER TABLE `rprj_todo_tipo` DISABLE KEYS */;
/*!40000 ALTER TABLE `rprj_todo_tipo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_users`
--

DROP TABLE IF EXISTS `rprj_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_users` (
  `id` varchar(16) NOT NULL,
  `login` varchar(255) NOT NULL,
  `pwd` varchar(255) NOT NULL,
  `pwd_salt` varchar(4) DEFAULT '',
  `fullname` text,
  `group_id` varchar(16) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `rprj_users_0` (`id`),
  KEY `rprj_users_1` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_users`
--

LOCK TABLES `rprj_users` WRITE;
/*!40000 ALTER TABLE `rprj_users` DISABLE KEYS */;
INSERT INTO `rprj_users` VALUES ('-1','adm','adm','','Administrator','-2');
/*!40000 ALTER TABLE `rprj_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rprj_users_groups`
--

DROP TABLE IF EXISTS `rprj_users_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rprj_users_groups` (
  `user_id` varchar(16) NOT NULL,
  `group_id` varchar(16) NOT NULL,
  PRIMARY KEY (`user_id`,`group_id`),
  KEY `rprj_users_groups_0` (`user_id`),
  KEY `rprj_users_groups_1` (`group_id`),
  KEY `rprj_users_groups_2` (`user_id`),
  KEY `rprj_users_groups_3` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rprj_users_groups`
--

LOCK TABLES `rprj_users_groups` WRITE;
/*!40000 ALTER TABLE `rprj_users_groups` DISABLE KEYS */;
INSERT INTO `rprj_users_groups` VALUES ('-1','-2'),('-1','-5'),('-1','-6');
/*!40000 ALTER TABLE `rprj_users_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_societa`
--

DROP TABLE IF EXISTS `test_societa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_societa` (
  `id` varchar(16) NOT NULL,
  `ragione_sociale` text,
  `indirizzo` text,
  `cap` varchar(6) DEFAULT NULL,
  `nazione` text,
  `telefono` text,
  `fax` text,
  `email` text,
  `note` text,
  `website` text,
  `citta` text,
  `provincia` text,
  `partita_iva` text,
  `tipo` text,
  `data_creazione` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `test_societa_0` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_societa`
--

LOCK TABLES `test_societa` WRITE;
/*!40000 ALTER TABLE `test_societa` DISABLE KEYS */;
/*!40000 ALTER TABLE `test_societa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_test_dblayer`
--

DROP TABLE IF EXISTS `test_test_dblayer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_test_dblayer` (
  `id` varchar(16) NOT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `descrizione` varchar(2000) DEFAULT NULL,
  `abilitato` char(1) DEFAULT NULL,
  `data_creazione` datetime DEFAULT NULL,
  `prezzo` float DEFAULT NULL,
  `data_disponibilita` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `test_test_dblayer_0` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_test_dblayer`
--

LOCK TABLES `test_test_dblayer` WRITE;
/*!40000 ALTER TABLE `test_test_dblayer` DISABLE KEYS */;
/*!40000 ALTER TABLE `test_test_dblayer` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-02-05 14:47:48
