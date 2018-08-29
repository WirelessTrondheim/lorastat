--
-- PostgreSQL database dump
--

-- Dumped from database version 9.6.10
-- Dumped by pg_dump version 9.6.10

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: log; Type: TABLE; Schema: public; Owner: lorastat
--

CREATE TABLE public.log (
    log_id bigint NOT NULL,
    lora_gw text,
    packets integer,
    bytes integer,
    "time" timestamp with time zone DEFAULT now()
);


ALTER TABLE public.log OWNER TO lorastat;

--
-- Name: log log_pkey; Type: CONSTRAINT; Schema: public; Owner: lorastat
--

ALTER TABLE ONLY public.log
    ADD CONSTRAINT log_pkey PRIMARY KEY (log_id);


--
-- Name: log_time_idx; Type: INDEX; Schema: public; Owner: lorastat
--

CREATE INDEX log_time_idx ON public.log USING btree ("time");


--
-- PostgreSQL database dump complete
--

