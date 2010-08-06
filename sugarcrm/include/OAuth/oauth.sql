create table oauth_consumer(
	c_key varchar(32) not null primary key,
	c_secret varchar(32),
	c_name text
);

create table oauth_tokens(
	c_token varchar(32) not null primary key,
	c_secret varchar(32),
	c_state tinyint,
	c_consumer varchar(32), 
	c_ts integer,
	c_verify varchar(32),
	c_authdata text,
	key(c_state, c_ts)
);

create table oauth_nonce(
	c_key varchar(32) not null,
	c_nonce varchar(32) not null,
	c_ts integer,
	primary key(c_key, c_nonce),
	key(c_key, c_ts)
);