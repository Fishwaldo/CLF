--
-- PostgreSQL database dump
--

\connect - postgres

--
-- TOC entry 1 (OID 0)
-- Name: TSyslog; Type: DATABASE; Schema: -; Owner: postgres
--

CREATE DATABASE "TSyslog" WITH TEMPLATE = template0 ENCODING = 0;


\connect "TSyslog" postgres

\connect - msyslog

SET search_path = public, pg_catalog;

--
-- TOC entry 2 (OID 4512536)
-- Name: syslog_tmail; Type: TABLE; Schema: public; Owner: msyslog
--

CREATE TABLE syslog_tmail (
    tmail_id bigserial NOT NULL,
    tmail_open integer,
    tmail_date date NOT NULL,
    tmail_time time without time zone NOT NULL,
    tlogin_id bigint NOT NULL
);


--
-- TOC entry 3 (OID 4512536)
-- Name: syslog_tmail; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_tmail FROM PUBLIC;


--
-- TOC entry 45 (OID 4512536)
-- Name: syslog_tmail_tmail_id_seq; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_tmail_tmail_id_seq FROM PUBLIC;


--
-- TOC entry 4 (OID 4512541)
-- Name: syslog_tlaunchqueue; Type: TABLE; Schema: public; Owner: msyslog
--

CREATE TABLE syslog_tlaunchqueue (
    tlaunchqueue_id bigserial NOT NULL,
    tlaunchqueue_desc character varying(256),
    tlaunch_id bigint NOT NULL,
    tmail_id bigint NOT NULL,
    tsyslog_id bigint NOT NULL
);


--
-- TOC entry 5 (OID 4512541)
-- Name: syslog_tlaunchqueue; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_tlaunchqueue FROM PUBLIC;


--
-- TOC entry 46 (OID 4512541)
-- Name: syslog_tlaunchqueue_tlaunchqueue_id_seq; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_tlaunchqueue_tlaunchqueue_id_seq FROM PUBLIC;


--
-- TOC entry 6 (OID 4512546)
-- Name: syslog_tsuspend; Type: TABLE; Schema: public; Owner: msyslog
--

CREATE TABLE syslog_tsuspend (
    tsuspend_id bigserial NOT NULL,
    tsuspend_status integer NOT NULL,
    tlogin_id bigint NOT NULL
);


--
-- TOC entry 47 (OID 4512546)
-- Name: syslog_tsuspend_tsuspend_id_seq; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_tsuspend_tsuspend_id_seq FROM PUBLIC;


--
-- TOC entry 7 (OID 4512551)
-- Name: syslog_temail; Type: TABLE; Schema: public; Owner: msyslog
--

CREATE TABLE syslog_temail (
    temail_id bigserial NOT NULL,
    temail_email character varying(80) NOT NULL,
    temail_desc character varying(256),
    tmail_id bigint NOT NULL,
    tsyslog_id bigint NOT NULL
);


--
-- TOC entry 8 (OID 4512551)
-- Name: syslog_temail; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_temail FROM PUBLIC;


--
-- TOC entry 48 (OID 4512551)
-- Name: syslog_temail_temail_id_seq; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_temail_temail_id_seq FROM PUBLIC;


--
-- TOC entry 9 (OID 4512556)
-- Name: tsyslog; Type: TABLE; Schema: public; Owner: msyslog
--

CREATE TABLE tsyslog (
    tsyslog_id bigserial NOT NULL,
    facility integer,
    severity integer,
    date date,
    "time" time without time zone,
    host character varying(128),
    message text
);


--
-- TOC entry 10 (OID 4512556)
-- Name: tsyslog; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE tsyslog FROM PUBLIC;


--
-- TOC entry 49 (OID 4512556)
-- Name: tsyslog_tsyslog_id_seq; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE tsyslog_tsyslog_id_seq FROM PUBLIC;


--
-- TOC entry 11 (OID 4512564)
-- Name: syslog_tarchive; Type: TABLE; Schema: public; Owner: msyslog
--

CREATE TABLE syslog_tarchive (
    tsyslog_id bigserial NOT NULL,
    facility integer,
    severity integer,
    date date,
    "time" time without time zone,
    host character varying(128),
    message text
);


--
-- TOC entry 12 (OID 4512564)
-- Name: syslog_tarchive; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_tarchive FROM PUBLIC;


--
-- TOC entry 50 (OID 4512564)
-- Name: syslog_tarchive_tsyslog_id_seq; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_tarchive_tsyslog_id_seq FROM PUBLIC;


--
-- TOC entry 13 (OID 4512572)
-- Name: syslog_tfilter; Type: TABLE; Schema: public; Owner: msyslog
--

CREATE TABLE syslog_tfilter (
    tfilter_id bigserial NOT NULL,
    tfilter_userorglobal integer NOT NULL,
    tfilter_desc character varying(128) NOT NULL,
    tlogin_id integer NOT NULL
);


--
-- TOC entry 14 (OID 4512572)
-- Name: syslog_tfilter; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_tfilter FROM PUBLIC;


--
-- TOC entry 51 (OID 4512572)
-- Name: syslog_tfilter_tfilter_id_seq; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_tfilter_tfilter_id_seq FROM PUBLIC;


--
-- TOC entry 15 (OID 4512577)
-- Name: syslog_tfilterdata; Type: TABLE; Schema: public; Owner: msyslog
--

CREATE TABLE syslog_tfilterdata (
    tfilterdata_id bigserial NOT NULL,
    tfilterdata_filter character varying(80),
    tfilterdata_include integer,
    tfilterdata_filterorlevel integer,
    tfilterdata_startfacility integer,
    tfilterdata_stopfacility integer,
    tfilterdata_startseverity integer,
    tfilterdata_stopseverity integer,
    tfilter_id bigint NOT NULL
);


--
-- TOC entry 16 (OID 4512577)
-- Name: syslog_tfilterdata; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_tfilterdata FROM PUBLIC;


--
-- TOC entry 17 (OID 4512582)
-- Name: syslog_tsave; Type: TABLE; Schema: public; Owner: msyslog
--

CREATE TABLE syslog_tsave (
    tsave_id bigserial NOT NULL,
    tsave_expiredate date NOT NULL,
    tsave_desc character varying(128),
    tsave_time time without time zone NOT NULL,
    tsave_date date NOT NULL,
    tlogin_id integer NOT NULL
);


--
-- TOC entry 18 (OID 4512582)
-- Name: syslog_tsave; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_tsave FROM PUBLIC;


--
-- TOC entry 52 (OID 4512582)
-- Name: syslog_tsave_tsave_id_seq; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_tsave_tsave_id_seq FROM PUBLIC;


--
-- TOC entry 19 (OID 4512587)
-- Name: syslog_tsavedata; Type: TABLE; Schema: public; Owner: msyslog
--

CREATE TABLE syslog_tsavedata (
    tsavedata_id bigserial NOT NULL,
    tsavedata_date date NOT NULL,
    tsavedata_time time without time zone NOT NULL,
    tsavedata_host character varying(128) NOT NULL,
    tsavedata_message text NOT NULL,
    tsavedata_facility integer,
    tsavedata_severity integer,
    tsave_id bigint NOT NULL
);


--
-- TOC entry 20 (OID 4512587)
-- Name: syslog_tsavedata; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_tsavedata FROM PUBLIC;


--
-- TOC entry 21 (OID 4512593)
-- Name: syslog_tprocess; Type: TABLE; Schema: public; Owner: msyslog
--

CREATE TABLE syslog_tprocess (
    tprocess_id bigint,
    thost_id bigint NOT NULL
);


--
-- TOC entry 22 (OID 4512593)
-- Name: syslog_tprocess; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_tprocess FROM PUBLIC;


--
-- TOC entry 23 (OID 4512597)
-- Name: syslog_thost; Type: TABLE; Schema: public; Owner: msyslog
--

CREATE TABLE syslog_thost (
    thost_id bigserial NOT NULL,
    thost_host character varying(128) NOT NULL,
    thost_alertexpire integer,
    thost_logexpire integer,
    thost_rate bigint,
    tpremadetype_id bigint NOT NULL,
    do_logreport bigint,
    log_reviewers bigint
);


--
-- TOC entry 24 (OID 4512597)
-- Name: syslog_thost; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_thost FROM PUBLIC;


--
-- TOC entry 53 (OID 4512597)
-- Name: syslog_thost_thost_id_seq; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_thost_thost_id_seq FROM PUBLIC;


--
-- TOC entry 25 (OID 4512602)
-- Name: syslog_tprocessorprofile; Type: TABLE; Schema: public; Owner: msyslog
--

CREATE TABLE syslog_tprocessorprofile (
    tprocessorprofile_id bigserial NOT NULL,
    thost_id bigint NOT NULL,
    tlogin_id bigint NOT NULL
);


--
-- TOC entry 26 (OID 4512602)
-- Name: syslog_tprocessorprofile; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_tprocessorprofile FROM PUBLIC;


--
-- TOC entry 27 (OID 4512607)
-- Name: syslog_tcustomerprofile; Type: TABLE; Schema: public; Owner: msyslog
--

CREATE TABLE syslog_tcustomerprofile (
    tcustomerprofile_id bigserial NOT NULL,
    tcustomerprofile_editrules bigint,
    thost_id bigint NOT NULL,
    tlogin_id bigint NOT NULL
);


--
-- TOC entry 28 (OID 4512607)
-- Name: syslog_tcustomerprofile; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_tcustomerprofile FROM PUBLIC;


--
-- TOC entry 29 (OID 4512612)
-- Name: syslog_tlaunch; Type: TABLE; Schema: public; Owner: msyslog
--

CREATE TABLE syslog_tlaunch (
    tlaunch_id bigserial NOT NULL,
    tlaunch_program text NOT NULL,
    tlaunch_longdesc text NOT NULL,
    tlaunch_shortdesc character varying(30) NOT NULL
);


--
-- TOC entry 30 (OID 4512612)
-- Name: syslog_tlaunch; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_tlaunch FROM PUBLIC;


--
-- TOC entry 54 (OID 4512612)
-- Name: syslog_tlaunch_tlaunch_id_seq; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_tlaunch_tlaunch_id_seq FROM PUBLIC;


--
-- TOC entry 31 (OID 4512620)
-- Name: syslog_talert; Type: TABLE; Schema: public; Owner: msyslog
--

CREATE TABLE syslog_talert (
    talert_id bigserial NOT NULL,
    talert_date date,
    talert_time time without time zone,
    talert_info character varying(80),
    tsyslog_id bigint
);


--
-- TOC entry 32 (OID 4512620)
-- Name: syslog_talert; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_talert FROM PUBLIC;


--
-- TOC entry 55 (OID 4512620)
-- Name: syslog_talert_talert_id_seq; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_talert_talert_id_seq FROM PUBLIC;


--
-- TOC entry 33 (OID 4512625)
-- Name: syslog_truledeny; Type: TABLE; Schema: public; Owner: msyslog
--

CREATE TABLE syslog_truledeny (
    truledeny_id bigserial NOT NULL,
    truledeny_expression character varying(80) NOT NULL,
    truledeny_startfacility integer,
    truledeny_stopfacility integer,
    truledeny_startseverity integer,
    truledeny_stopseverity integer,
    trule_id bigint
);


--
-- TOC entry 34 (OID 4512625)
-- Name: syslog_truledeny; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_truledeny FROM PUBLIC;


--
-- TOC entry 35 (OID 4512630)
-- Name: syslog_trule; Type: TABLE; Schema: public; Owner: msyslog
--

CREATE TABLE syslog_trule (
    trule_id bigserial NOT NULL,
    trule_logalert integer,
    trule_email character varying(80),
    trule_expression character varying(80),
    trule_desc character varying(256),
    trule_ruleorlevel integer,
    trule_startfacility integer,
    trule_stopfacility integer,
    trule_startseverity integer,
    trule_stopseverity integer,
    trule_threshold integer,
    trule_thresholdtype integer,
    trule_starttime bigint,
    trule_endtime bigint,
    trule_timertype integer,
    trule_daysofweek integer,
    tlaunch_id bigint,
    thost_id bigint NOT NULL
);


--
-- TOC entry 36 (OID 4512630)
-- Name: syslog_trule; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_trule FROM PUBLIC;


--
-- TOC entry 56 (OID 4512630)
-- Name: syslog_trule_trule_id_seq; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_trule_trule_id_seq FROM PUBLIC;


--
-- TOC entry 37 (OID 4512635)
-- Name: syslog_tpremadetype; Type: TABLE; Schema: public; Owner: msyslog
--

CREATE TABLE syslog_tpremadetype (
    tpremadetype_id bigserial NOT NULL,
    tpremadetype_desc character varying(40) NOT NULL,
    logwatch_cmd text
);


--
-- TOC entry 38 (OID 4512635)
-- Name: syslog_tpremadetype; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_tpremadetype FROM PUBLIC;


--
-- TOC entry 39 (OID 4512643)
-- Name: syslog_tpremadedeny; Type: TABLE; Schema: public; Owner: msyslog
--

CREATE TABLE syslog_tpremadedeny (
    tpremadedeny_id bigserial NOT NULL,
    tpremadedeny_expression character varying(80) NOT NULL,
    tpremadedeny_startfacility integer,
    tpremadedeny_stopfacility integer,
    tpremadedeny_startseverity integer,
    tpremadedeny_stopseverity integer,
    tpremade_id bigint
);


--
-- TOC entry 40 (OID 4512643)
-- Name: syslog_tpremadedeny; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_tpremadedeny FROM PUBLIC;


--
-- TOC entry 41 (OID 4512648)
-- Name: syslog_tpremade; Type: TABLE; Schema: public; Owner: msyslog
--

CREATE TABLE syslog_tpremade (
    tpremade_id bigserial NOT NULL,
    tpremade_code character varying(80) NOT NULL,
    tpremade_desc text,
    tpremade_premadeorlevel integer,
    tpremade_startfacility integer,
    tpremade_stopfacility integer,
    tpremade_startseverity integer,
    tpremade_stopseverity integer,
    tpremadetype_id bigint,
    tpremade_threshold integer,
    tpremade_thresholdtype integer,
    tlaunch_id bigint
);


--
-- TOC entry 42 (OID 4512648)
-- Name: syslog_tpremade; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_tpremade FROM PUBLIC;


--
-- TOC entry 57 (OID 4512648)
-- Name: syslog_tpremade_tpremade_id_seq; Type: ACL; Schema: public; Owner: msyslog
--

REVOKE ALL ON TABLE syslog_tpremade_tpremade_id_seq FROM PUBLIC;


--
-- TOC entry 43 (OID 4512656)
-- Name: syslog_tsummary; Type: TABLE; Schema: public; Owner: msyslog
--

CREATE TABLE syslog_tsummary (
    tsummary_id serial NOT NULL,
    host character varying(128),
    date date,
    data text
);


--
-- TOC entry 44 (OID 4512664)
-- Name: syslog_treview; Type: TABLE; Schema: public; Owner: msyslog
--

CREATE TABLE syslog_treview (
    id serial NOT NULL,
    reviewer bigint,
    date timestamp without time zone,
    tsummary_id bigint,
    comments text
);


--
-- TOC entry 59 (OID 7875235)
-- Name: syslog_tmail_tlogin_id; Type: INDEX; Schema: public; Owner: msyslog
--

CREATE UNIQUE INDEX syslog_tmail_tlogin_id ON syslog_tmail USING btree (tlogin_id);


--
-- TOC entry 63 (OID 7875236)
-- Name: host_idx; Type: INDEX; Schema: public; Owner: msyslog
--

CREATE INDEX host_idx ON tsyslog USING btree (host);


--
-- TOC entry 66 (OID 7875237)
-- Name: tsyslogdatetime_idx; Type: INDEX; Schema: public; Owner: msyslog
--

CREATE INDEX tsyslogdatetime_idx ON tsyslog USING btree (date, "time");


--
-- TOC entry 64 (OID 7875238)
-- Name: tsyslhostid_idx; Type: INDEX; Schema: public; Owner: msyslog
--

CREATE INDEX tsyslhostid_idx ON tsyslog USING btree (tsyslog_id, host);


--
-- TOC entry 67 (OID 7875239)
-- Name: archhost_idx; Type: INDEX; Schema: public; Owner: msyslog
--

CREATE INDEX archhost_idx ON syslog_tarchive USING btree (host);


--
-- TOC entry 69 (OID 7875240)
-- Name: tarchdatetime_idx; Type: INDEX; Schema: public; Owner: msyslog
--

CREATE INDEX tarchdatetime_idx ON syslog_tarchive USING btree (date, "time");


--
-- TOC entry 70 (OID 7875241)
-- Name: tarchhostid_idx; Type: INDEX; Schema: public; Owner: msyslog
--

CREATE INDEX tarchhostid_idx ON syslog_tarchive USING btree (tsyslog_id, host);


--
-- TOC entry 75 (OID 7875242)
-- Name: tsavedata_saveid_idx; Type: INDEX; Schema: public; Owner: msyslog
--

CREATE INDEX tsavedata_saveid_idx ON syslog_tsavedata USING btree (tsave_id);


--
-- TOC entry 78 (OID 7875243)
-- Name: tprocessorprofile_tlogin_id_idx; Type: INDEX; Schema: public; Owner: msyslog
--

CREATE INDEX tprocessorprofile_tlogin_id_idx ON syslog_tprocessorprofile USING btree (tlogin_id);


--
-- TOC entry 80 (OID 7875244)
-- Name: tcustomerprofile_tlogin_id_idx; Type: INDEX; Schema: public; Owner: msyslog
--

CREATE INDEX tcustomerprofile_tlogin_id_idx ON syslog_tcustomerprofile USING btree (tlogin_id);


--
-- TOC entry 82 (OID 7875245)
-- Name: tlaunch_shortdesc_idx; Type: INDEX; Schema: public; Owner: msyslog
--

CREATE UNIQUE INDEX tlaunch_shortdesc_idx ON syslog_tlaunch USING btree (tlaunch_shortdesc);


--
-- TOC entry 84 (OID 7875246)
-- Name: talert_tsyslog_id_idx; Type: INDEX; Schema: public; Owner: msyslog
--

CREATE UNIQUE INDEX talert_tsyslog_id_idx ON syslog_talert USING btree (tsyslog_id);


--
-- TOC entry 86 (OID 7875247)
-- Name: trule_id_deny_idx; Type: INDEX; Schema: public; Owner: msyslog
--

CREATE INDEX trule_id_deny_idx ON syslog_truledeny USING btree (trule_id);


--
-- TOC entry 88 (OID 7875248)
-- Name: trule_host_idx; Type: INDEX; Schema: public; Owner: msyslog
--

CREATE INDEX trule_host_idx ON syslog_trule USING btree (thost_id);


--
-- TOC entry 91 (OID 7875249)
-- Name: tpremade_id_deny_idx; Type: INDEX; Schema: public; Owner: msyslog
--

CREATE INDEX tpremade_id_deny_idx ON syslog_tpremadedeny USING btree (tpremade_id);


--
-- TOC entry 93 (OID 7875250)
-- Name: tpremadetype_id2_idx; Type: INDEX; Schema: public; Owner: msyslog
--

CREATE INDEX tpremadetype_id2_idx ON syslog_tpremade USING btree (tpremadetype_id);


--
-- TOC entry 58 (OID 7875251)
-- Name: syslog_tmail_pkey; Type: CONSTRAINT; Schema: public; Owner: msyslog
--

ALTER TABLE ONLY syslog_tmail
    ADD CONSTRAINT syslog_tmail_pkey PRIMARY KEY (tmail_id);


--
-- TOC entry 60 (OID 7875253)
-- Name: syslog_tlaunchqueue_pkey; Type: CONSTRAINT; Schema: public; Owner: msyslog
--

ALTER TABLE ONLY syslog_tlaunchqueue
    ADD CONSTRAINT syslog_tlaunchqueue_pkey PRIMARY KEY (tlaunchqueue_id);


--
-- TOC entry 61 (OID 7875255)
-- Name: syslog_tsuspend_pkey; Type: CONSTRAINT; Schema: public; Owner: msyslog
--

ALTER TABLE ONLY syslog_tsuspend
    ADD CONSTRAINT syslog_tsuspend_pkey PRIMARY KEY (tsuspend_id);


--
-- TOC entry 62 (OID 7875257)
-- Name: syslog_temail_pkey; Type: CONSTRAINT; Schema: public; Owner: msyslog
--

ALTER TABLE ONLY syslog_temail
    ADD CONSTRAINT syslog_temail_pkey PRIMARY KEY (temail_id);


--
-- TOC entry 65 (OID 7875259)
-- Name: tsyslog_pkey; Type: CONSTRAINT; Schema: public; Owner: msyslog
--

ALTER TABLE ONLY tsyslog
    ADD CONSTRAINT tsyslog_pkey PRIMARY KEY (tsyslog_id);


--
-- TOC entry 68 (OID 7875261)
-- Name: syslog_tarchive_pkey; Type: CONSTRAINT; Schema: public; Owner: msyslog
--

ALTER TABLE ONLY syslog_tarchive
    ADD CONSTRAINT syslog_tarchive_pkey PRIMARY KEY (tsyslog_id);


--
-- TOC entry 71 (OID 7875263)
-- Name: syslog_tfilter_pkey; Type: CONSTRAINT; Schema: public; Owner: msyslog
--

ALTER TABLE ONLY syslog_tfilter
    ADD CONSTRAINT syslog_tfilter_pkey PRIMARY KEY (tfilter_id);


--
-- TOC entry 72 (OID 7875265)
-- Name: syslog_tfilterdata_pkey; Type: CONSTRAINT; Schema: public; Owner: msyslog
--

ALTER TABLE ONLY syslog_tfilterdata
    ADD CONSTRAINT syslog_tfilterdata_pkey PRIMARY KEY (tfilterdata_id);


--
-- TOC entry 73 (OID 7875267)
-- Name: syslog_tsave_pkey; Type: CONSTRAINT; Schema: public; Owner: msyslog
--

ALTER TABLE ONLY syslog_tsave
    ADD CONSTRAINT syslog_tsave_pkey PRIMARY KEY (tsave_id);


--
-- TOC entry 74 (OID 7875269)
-- Name: syslog_tsavedata_pkey; Type: CONSTRAINT; Schema: public; Owner: msyslog
--

ALTER TABLE ONLY syslog_tsavedata
    ADD CONSTRAINT syslog_tsavedata_pkey PRIMARY KEY (tsavedata_id);


--
-- TOC entry 76 (OID 7875271)
-- Name: syslog_thost_pkey; Type: CONSTRAINT; Schema: public; Owner: msyslog
--

ALTER TABLE ONLY syslog_thost
    ADD CONSTRAINT syslog_thost_pkey PRIMARY KEY (thost_id);


--
-- TOC entry 77 (OID 7875273)
-- Name: syslog_tprocessorprofile_pkey; Type: CONSTRAINT; Schema: public; Owner: msyslog
--

ALTER TABLE ONLY syslog_tprocessorprofile
    ADD CONSTRAINT syslog_tprocessorprofile_pkey PRIMARY KEY (tprocessorprofile_id);


--
-- TOC entry 79 (OID 7875275)
-- Name: syslog_tcustomerprofile_pkey; Type: CONSTRAINT; Schema: public; Owner: msyslog
--

ALTER TABLE ONLY syslog_tcustomerprofile
    ADD CONSTRAINT syslog_tcustomerprofile_pkey PRIMARY KEY (tcustomerprofile_id);


--
-- TOC entry 81 (OID 7875277)
-- Name: syslog_tlaunch_pkey; Type: CONSTRAINT; Schema: public; Owner: msyslog
--

ALTER TABLE ONLY syslog_tlaunch
    ADD CONSTRAINT syslog_tlaunch_pkey PRIMARY KEY (tlaunch_id);


--
-- TOC entry 83 (OID 7875279)
-- Name: syslog_talert_pkey; Type: CONSTRAINT; Schema: public; Owner: msyslog
--

ALTER TABLE ONLY syslog_talert
    ADD CONSTRAINT syslog_talert_pkey PRIMARY KEY (talert_id);


--
-- TOC entry 85 (OID 7875281)
-- Name: syslog_truledeny_pkey; Type: CONSTRAINT; Schema: public; Owner: msyslog
--

ALTER TABLE ONLY syslog_truledeny
    ADD CONSTRAINT syslog_truledeny_pkey PRIMARY KEY (truledeny_id);


--
-- TOC entry 87 (OID 7875283)
-- Name: syslog_trule_pkey; Type: CONSTRAINT; Schema: public; Owner: msyslog
--

ALTER TABLE ONLY syslog_trule
    ADD CONSTRAINT syslog_trule_pkey PRIMARY KEY (trule_id);


--
-- TOC entry 89 (OID 7875285)
-- Name: syslog_tpremadetype_pkey; Type: CONSTRAINT; Schema: public; Owner: msyslog
--

ALTER TABLE ONLY syslog_tpremadetype
    ADD CONSTRAINT syslog_tpremadetype_pkey PRIMARY KEY (tpremadetype_id);


--
-- TOC entry 90 (OID 7875287)
-- Name: syslog_tpremadedeny_pkey; Type: CONSTRAINT; Schema: public; Owner: msyslog
--

ALTER TABLE ONLY syslog_tpremadedeny
    ADD CONSTRAINT syslog_tpremadedeny_pkey PRIMARY KEY (tpremadedeny_id);


--
-- TOC entry 92 (OID 7875289)
-- Name: syslog_tpremade_pkey; Type: CONSTRAINT; Schema: public; Owner: msyslog
--

ALTER TABLE ONLY syslog_tpremade
    ADD CONSTRAINT syslog_tpremade_pkey PRIMARY KEY (tpremade_id);


