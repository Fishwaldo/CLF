--
-- PostgreSQL database dump
--

\connect - secframe

SET search_path = public, pg_catalog;

--
-- Data for TOC entry 7 (OID 7875320)
-- Name: secframe_tqueue; Type: TABLE DATA; Schema: public; Owner: secframe
--

COPY secframe_tqueue (tqueue_id, tqueue_command, tqueue_date, tqueue_time, tqueue_dateprocessed, tqueue_timeprocessed, tqueue_processed, tqueue_data1, tqueue_data2) FROM stdin;
\.


--
-- Data for TOC entry 8 (OID 7875328)
-- Name: secframe_tlogin; Type: TABLE DATA; Schema: public; Owner: secframe
--

COPY secframe_tlogin (tlogin_id, tlogin_username, tlogin_password, tlogin_name, tlogin_email, tlogin_home, tlogin_work, tlogin_cell, tlogin_pager, tlogin_address1, tlogin_address2, tlogin_city, tlogin_state, tlogin_zip) FROM stdin;
3	msyslog	5f4dcc3b5aa765d61d8327deb882cf99	msyslog User	root@localhost									
1	clfadmin	5f4dcc3b5aa765d61d8327deb882cf99	Sample User	samplemail@yahoo.com									
\.


--
-- Data for TOC entry 9 (OID 7875333)
-- Name: secframe_tgroup; Type: TABLE DATA; Schema: public; Owner: secframe
--

COPY secframe_tgroup (tgroup_id, tgroup_name, tgroup_desc) FROM stdin;
1	Everyone	All Users
3	Normal Users	Standard System Users
2	Administrators	System Administrators
8	Syslog Customer	Customers of Syslog System
9	Syslog Analyst	NOC Analyst
10	Syslog Administrators	Syslog Adminstrator
11	Syslog msyslog	Syslog Processor
\.


--
-- Data for TOC entry 10 (OID 7875338)
-- Name: secframe_tgroupmembers; Type: TABLE DATA; Schema: public; Owner: secframe
--

COPY secframe_tgroupmembers (tgroupmembers_id, tlogin_id, tgroup_id) FROM stdin;
1	1	1
2	1	2
3	1	3
8	3	1
11	1	10
16	3	11
\.


--
-- Data for TOC entry 11 (OID 7875343)
-- Name: secframe_tapp; Type: TABLE DATA; Schema: public; Owner: secframe
--

COPY secframe_tapp (tapp_id, tapp_name, tapp_desc) FROM stdin;
1	User Administrators	Administrators Access-List
2	SyslogOp	Syslog Access-List
\.


--
-- Data for TOC entry 12 (OID 7875348)
-- Name: secframe_tappperm; Type: TABLE DATA; Schema: public; Owner: secframe
--

COPY secframe_tappperm (tappperm_id, tappperm_usergroup, tappperm_ugid, tappperm_allowaccess, tappperm_priority, tapp_id) FROM stdin;
1	2	1	0	1	2
6	2	1	1	2	2
\.


--
-- TOC entry 1 (OID 7875318)
-- Name: tqueue_seq; Type: SEQUENCE SET; Schema: public; Owner: secframe
--

SELECT pg_catalog.setval ('tqueue_seq', 1, false);


--
-- TOC entry 2 (OID 7875326)
-- Name: tlogin_seq; Type: SEQUENCE SET; Schema: public; Owner: secframe
--

SELECT pg_catalog.setval ('tlogin_seq', 41, true);


--
-- TOC entry 3 (OID 7875331)
-- Name: tgroup_seq; Type: SEQUENCE SET; Schema: public; Owner: secframe
--

SELECT pg_catalog.setval ('tgroup_seq', 11, true);


--
-- TOC entry 4 (OID 7875336)
-- Name: tgroupmembers_seq; Type: SEQUENCE SET; Schema: public; Owner: secframe
--

SELECT pg_catalog.setval ('tgroupmembers_seq', 60, true);


--
-- TOC entry 5 (OID 7875341)
-- Name: tapp_seq; Type: SEQUENCE SET; Schema: public; Owner: secframe
--

SELECT pg_catalog.setval ('tapp_seq', 2, true);


--
-- TOC entry 6 (OID 7875346)
-- Name: tappperm_seq; Type: SEQUENCE SET; Schema: public; Owner: secframe
--

SELECT pg_catalog.setval ('tappperm_seq', 38, true);


