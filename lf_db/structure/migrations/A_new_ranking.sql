-- modify existing lf_ranking table
ALTER TABLE lf_ranking
  ADD singles_r TINYINT UNSIGNED DEFAULT NULL ,
  ADD doubles_r TINYINT UNSIGNED DEFAULT NULL ,
  ADD mixed_r TINYINT UNSIGNED DEFAULT NULL;


-- TO HAVE EXAMPLE DATA AVAILABLE
update lf_ranking set singles_r=null, doubles_r=null, mixed_r=null;
update lf_ranking
  set singles_r=CASE
    WHEN singles='A' THEN 2
    WHEN singles='B1' THEN 4
    WHEN singles='B2' THEN 6
    WHEN singles='C1' THEN 8
    WHEN singles='C2' THEN 10
    WHEN singles='D' THEN 12
  END,
  doubles_r=CASE
    WHEN doubles='A' THEN 2
    WHEN doubles='B1' THEN 4
    WHEN doubles='B2' THEN 6
    WHEN doubles='C1' THEN 8
    WHEN doubles='C2' THEN 10
    WHEN doubles='D' THEN 12
  END,
  mixed_r=CASE
    WHEN mixed='A' THEN 2
    WHEN mixed='B1' THEN 4
    WHEN mixed='B2' THEN 6
    WHEN mixed='C1' THEN 8
    WHEN mixed='C2' THEN 10
    WHEN mixed='D' THEN 12
  END
  WHERE date != '2019-05-15';

-- TO HAVE EXAMPLE DATA AVAILABLE, move ontmoetings date to this year
update lf_match
 set `date`= DATE_ADD(`date`, INTERVAL 365 DAY);