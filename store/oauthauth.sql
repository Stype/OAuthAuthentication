CREATE TABLE /*_*/oauthauth_user (
  `oaau_rid` int(10) unsigned NOT NULL,
  `oaau_uid` int(10) unsigned NOT NULL PRIMARY KEY,
  `oaau_username` varchar(255) binary not null
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/idx_rid ON /*_*/oauthauth_user (`oaau_rid`);

