--
-- PostgreSQL database dump
--

\connect - secframe

--
-- TOC entry 1 (OID 0)
-- Name: securityframework; Type: DATABASE; Schema: -; Owner: secframe
--

CREATE DATABASE securityframework WITH TEMPLATE = template0 ENCODING = 0;


\connect securityframework secframe

SET search_path = public, pg_catalog;

--
-- TOC entry 2 (OID 7875318)
-- Name: tqueue_seq; Type: SEQUENCE; Schema: public; Owner: secframe
--

CREATE SEQUENCE tqueue_seq
    START 1
    INCREMENT 1
    MAXVALUE 9223372036854775807
    MINVALUE 1
    CACHE 1;


--
-- TOC entry 3 (OID 7875318)
-- Name: tqueue_seq; Type: ACL; Schema: public; Owner: secframe
--

REVOKE ALL ON TABLE tqueue_seq FROM PUBLIC;


--
-- TOC entry 14 (OID 7875320)
-- Name: secframe_tqueue; Type: TABLE; Schema: public; Owner: secframe
--

CREATE TABLE secframe_tqueue (
    tqueue_id integer DEFAULT nextval('TQueue_Seq'::text),
    tqueue_command character varying(16) NOT NULL,
    tqueue_date date NOT NULL,
    tqueue_time time without time zone NOT NULL,
    tqueue_dateprocessed date,
    tqueue_timeprocessed time without time zone,
    tqueue_processed integer,
    tqueue_data1 text,
    tqueue_data2 text
);


--
-- TOC entry 15 (OID 7875320)
-- Name: secframe_tqueue; Type: ACL; Schema: public; Owner: secframe
--

REVOKE ALL ON TABLE secframe_tqueue FROM PUBLIC;


--
-- TOC entry 4 (OID 7875326)
-- Name: tlogin_seq; Type: SEQUENCE; Schema: public; Owner: secframe
--

CREATE SEQUENCE tlogin_seq
    START 1
    INCREMENT 1
    MAXVALUE 9223372036854775807
    MINVALUE 1
    CACHE 1;


--
-- TOC entry 5 (OID 7875326)
-- Name: tlogin_seq; Type: ACL; Schema: public; Owner: secframe
--

REVOKE ALL ON TABLE tlogin_seq FROM PUBLIC;


--
-- TOC entry 16 (OID 7875328)
-- Name: secframe_tlogin; Type: TABLE; Schema: public; Owner: secframe
--

CREATE TABLE secframe_tlogin (
    tlogin_id integer DEFAULT nextval('TLogin_Seq'::text),
    tlogin_username character varying(128) NOT NULL,
    tlogin_password character varying(32) NOT NULL,
    tlogin_name character varying(40) NOT NULL,
    tlogin_email character varying(40) NOT NULL,
    tlogin_home character varying(20),
    tlogin_work character varying(20),
    tlogin_cell character varying(20),
    tlogin_pager character varying(20),
    tlogin_address1 character varying(40),
    tlogin_address2 character varying(40),
    tlogin_city character varying(40),
    tlogin_state character varying(2),
    tlogin_zip character varying(12)
);


--
-- TOC entry 17 (OID 7875328)
-- Name: secframe_tlogin; Type: ACL; Schema: public; Owner: secframe
--

REVOKE ALL ON TABLE secframe_tlogin FROM PUBLIC;


--
-- TOC entry 6 (OID 7875331)
-- Name: tgroup_seq; Type: SEQUENCE; Schema: public; Owner: secframe
--

CREATE SEQUENCE tgroup_seq
    START 1
    INCREMENT 1
    MAXVALUE 9223372036854775807
    MINVALUE 1
    CACHE 1;


--
-- TOC entry 7 (OID 7875331)
-- Name: tgroup_seq; Type: ACL; Schema: public; Owner: secframe
--

REVOKE ALL ON TABLE tgroup_seq FROM PUBLIC;


--
-- TOC entry 18 (OID 7875333)
-- Name: secframe_tgroup; Type: TABLE; Schema: public; Owner: secframe
--

CREATE TABLE secframe_tgroup (
    tgroup_id integer DEFAULT nextval('TGroup_Seq'::text),
    tgroup_name character varying(30) NOT NULL,
    tgroup_desc character varying(80) NOT NULL
);


--
-- TOC entry 19 (OID 7875333)
-- Name: secframe_tgroup; Type: ACL; Schema: public; Owner: secframe
--

REVOKE ALL ON TABLE secframe_tgroup FROM PUBLIC;


--
-- TOC entry 8 (OID 7875336)
-- Name: tgroupmembers_seq; Type: SEQUENCE; Schema: public; Owner: secframe
--

CREATE SEQUENCE tgroupmembers_seq
    START 1
    INCREMENT 1
    MAXVALUE 9223372036854775807
    MINVALUE 1
    CACHE 1;


--
-- TOC entry 9 (OID 7875336)
-- Name: tgroupmembers_seq; Type: ACL; Schema: public; Owner: secframe
--

REVOKE ALL ON TABLE tgroupmembers_seq FROM PUBLIC;


--
-- TOC entry 20 (OID 7875338)
-- Name: secframe_tgroupmembers; Type: TABLE; Schema: public; Owner: secframe
--

CREATE TABLE secframe_tgroupmembers (
    tgroupmembers_id integer DEFAULT nextval('TGroupMembers_Seq'::text),
    tlogin_id integer NOT NULL,
    tgroup_id integer NOT NULL
);


--
-- TOC entry 21 (OID 7875338)
-- Name: secframe_tgroupmembers; Type: ACL; Schema: public; Owner: secframe
--

REVOKE ALL ON TABLE secframe_tgroupmembers FROM PUBLIC;


--
-- TOC entry 10 (OID 7875341)
-- Name: tapp_seq; Type: SEQUENCE; Schema: public; Owner: secframe
--

CREATE SEQUENCE tapp_seq
    START 1
    INCREMENT 1
    MAXVALUE 9223372036854775807
    MINVALUE 1
    CACHE 1;


--
-- TOC entry 11 (OID 7875341)
-- Name: tapp_seq; Type: ACL; Schema: public; Owner: secframe
--

REVOKE ALL ON TABLE tapp_seq FROM PUBLIC;


--
-- TOC entry 22 (OID 7875343)
-- Name: secframe_tapp; Type: TABLE; Schema: public; Owner: secframe
--

CREATE TABLE secframe_tapp (
    tapp_id integer DEFAULT nextval('TApp_Seq'::text),
    tapp_name character varying(30) NOT NULL,
    tapp_desc character varying(80) NOT NULL
);


--
-- TOC entry 23 (OID 7875343)
-- Name: secframe_tapp; Type: ACL; Schema: public; Owner: secframe
--

REVOKE ALL ON TABLE secframe_tapp FROM PUBLIC;


--
-- TOC entry 12 (OID 7875346)
-- Name: tappperm_seq; Type: SEQUENCE; Schema: public; Owner: secframe
--

CREATE SEQUENCE tappperm_seq
    START 1
    INCREMENT 1
    MAXVALUE 9223372036854775807
    MINVALUE 1
    CACHE 1;


--
-- TOC entry 13 (OID 7875346)
-- Name: tappperm_seq; Type: ACL; Schema: public; Owner: secframe
--

REVOKE ALL ON TABLE tappperm_seq FROM PUBLIC;


--
-- TOC entry 24 (OID 7875348)
-- Name: secframe_tappperm; Type: TABLE; Schema: public; Owner: secframe
--

CREATE TABLE secframe_tappperm (
    tappperm_id integer DEFAULT nextval('TAppPerm_Seq'::text),
    tappperm_usergroup integer NOT NULL,
    tappperm_ugid integer NOT NULL,
    tappperm_allowaccess integer NOT NULL,
    tappperm_priority integer NOT NULL,
    tapp_id integer NOT NULL
);


--
-- TOC entry 25 (OID 7875348)
-- Name: secframe_tappperm; Type: ACL; Schema: public; Owner: secframe
--

REVOKE ALL ON TABLE secframe_tappperm FROM PUBLIC;


--
-- TOC entry 27 (OID 7875383)
-- Name: tqueue_id_idx; Type: INDEX; Schema: public; Owner: secframe
--

CREATE UNIQUE INDEX tqueue_id_idx ON secframe_tqueue USING btree (tqueue_id);


--
-- TOC entry 26 (OID 7875384)
-- Name: tqueue_command_idx; Type: INDEX; Schema: public; Owner: secframe
--

CREATE INDEX tqueue_command_idx ON secframe_tqueue USING btree (tqueue_command);


--
-- TOC entry 28 (OID 7875385)
-- Name: tlogin_id_idx; Type: INDEX; Schema: public; Owner: secframe
--

CREATE UNIQUE INDEX tlogin_id_idx ON secframe_tlogin USING btree (tlogin_id);


--
-- TOC entry 29 (OID 7875386)
-- Name: tlogin_username_idx; Type: INDEX; Schema: public; Owner: secframe
--

CREATE UNIQUE INDEX tlogin_username_idx ON secframe_tlogin USING btree (tlogin_username);


--
-- TOC entry 30 (OID 7875387)
-- Name: tgroup_id_idx; Type: INDEX; Schema: public; Owner: secframe
--

CREATE UNIQUE INDEX tgroup_id_idx ON secframe_tgroup USING btree (tgroup_id);


--
-- TOC entry 31 (OID 7875388)
-- Name: tgroupmembers_id_idx; Type: INDEX; Schema: public; Owner: secframe
--

CREATE UNIQUE INDEX tgroupmembers_id_idx ON secframe_tgroupmembers USING btree (tgroupmembers_id);


--
-- TOC entry 32 (OID 7875389)
-- Name: tapp_id_idx; Type: INDEX; Schema: public; Owner: secframe
--

CREATE UNIQUE INDEX tapp_id_idx ON secframe_tapp USING btree (tapp_id);


--
-- TOC entry 34 (OID 7875390)
-- Name: tappperm_id_idx; Type: INDEX; Schema: public; Owner: secframe
--

CREATE UNIQUE INDEX tappperm_id_idx ON secframe_tappperm USING btree (tappperm_id);


--
-- TOC entry 37 (OID 7875391)
-- Name: tappperm_usergroup_idx; Type: INDEX; Schema: public; Owner: secframe
--

CREATE INDEX tappperm_usergroup_idx ON secframe_tappperm USING btree (tappperm_usergroup);


--
-- TOC entry 36 (OID 7875392)
-- Name: tappperm_ugid_idx; Type: INDEX; Schema: public; Owner: secframe
--

CREATE INDEX tappperm_ugid_idx ON secframe_tappperm USING btree (tappperm_ugid);


--
-- TOC entry 33 (OID 7875393)
-- Name: tappperm_allowaccess_idx; Type: INDEX; Schema: public; Owner: secframe
--

CREATE INDEX tappperm_allowaccess_idx ON secframe_tappperm USING btree (tappperm_allowaccess);


--
-- TOC entry 35 (OID 7875394)
-- Name: tappperm_tapp_id_idx; Type: INDEX; Schema: public; Owner: secframe
--

CREATE INDEX tappperm_tapp_id_idx ON secframe_tappperm USING btree (tapp_id);


