PBO_USERNAME=''
PBO_PWD=''
if [ -r ./secret.cfg ] ; then
	. ./secret.cfg
fi
PBO_COMPETITIE_ID='2360CDE9-4A6F-4E50-BF49-E11F8B1D0772'
PBO_COMPETITIE_START_DAY='20150801'
PBO_COMPETITIE_END_DAY='20160731'
PBO_OVL_ID='638D0B55-C39B-43AB-8A9D-D50D62017FBE'
PBO_OVL_GID='3825E3C5-1371-4FF6-94AF-C4A3B152802A'

USER_AGENT='Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2.11) Gecko/20101012 Firefox/3.6.11'
COOKIES_PATH='../data/tmp/cookie.txt'

rm ../data/tmp/*

# Setting cookie to bypass "do you accept cookies" permission
echo "#HttpOnly_www.toernooi.nl	FALSE	/	FALSE	0	st	c=1" >> $COOKIES_PATH

curl \
    --location \
    --user-agent "$USER_AGENT" \
    --cookie "$COOKIES_PATH" \
    --cookie-jar "$COOKIES_PATH" \
    --data '__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=fFOeUm3GO%2FP3HmG%2B6wDEKx%2BJjHmR%2Fe%2B777hXIxwWE8XADUStBETa%2FdJZvxXOzwqQ082wjyUPdnM2M7j%2FxMwxFgsBCMSRyldJ3%2FDany5SWaBG3eb6cPJ%2FH5AlH5zO3Wc06q9h3oX30465TAD6Mz%2F7MX0lPODX2abWB%2FkUNEhuh6psqLT5wNF3jFKJ0ldyGRjWvTP5KYIvSCAiGlmm3LN8dQP8pce1%2BxykmbH%2BGUMWzD1THWgEGC1A5lms7rsLlxQu30HsBCHkZjGmZovdbS230StheNPAjTUsZeJppYUdT2ldCncB%2BRG3TkGMeJYDK4Ke6VPgwkhpMM%2F0X%2BLRfEJ4f9nxQ4C9%2B3kJLhTjIQgcMgUNNbV%2B&__VIEWSTATEGENERATOR=625BA342&__EVENTVALIDATION=DIEVYDQ3qhCtNktK5qVeyedQBuuht%2Bv1YFHJ1NEEOrfA8G80AX6%2B5sfTsRT4PdwzorA1h9znDIRFhfj1jYoKWy0k3O4zDC00YfhYyS87IwSMuj0cmXbbXX%2F26jpHy%2FF%2BhJpfgPbuHaMsJVrE8rHdlzrqQ0q7DOnDXPXLePGPLRPFHzCp70PDacAX%2B0%2B9k2viEwd9ynve1pYyJfmNJ1YsASalCuzYpjbdydQrz3PQIYLOeE9AT5%2BSBEZc3NLHoIV%2BdDLo6Q%3D%3D&tbxSearchQuery=Zoek...&ctl00%24ctl00%24ctl00%24cphPage%24ddlSearchType=1&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24UserName='"$PBO_USERNAME"'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24Password='"$PBO_PWD"'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24LoginButton=Inloggen' \
    --output '../data/tmp/login.txt' \
    'https://www.toernooi.nl/member/login.aspx'

curl \
    --silent \
    --location \
    --user-agent "$USER_AGENT" \
    --cookie "$COOKIES_PATH" \
    --cookie-jar "$COOKIES_PATH" \
    --output "../data/tmp/teams.csv" \
    'https://www.toernooi.nl/sport/admin/exportteams.aspx?id='"$PBO_COMPETITIE_ID"'&ft=1'

curl \
    --silent \
    --location \
    --user-agent "$USER_AGENT" \
    --cookie "$COOKIES_PATH" \
    --cookie-jar "$COOKIES_PATH" \
    --output "../data/tmp/clubs.csv" \
    'https://www.toernooi.nl/organization/export/group_subgroups_export.aspx?id='"$PBO_OVL_ID"'&gid='"$PBO_OVL_GID"'&ft=1'

curl \
    --silent \
    --location \
    --user-agent "$USER_AGENT" \
    --cookie "$COOKIES_PATH" \
    --cookie-jar "$COOKIES_PATH" \
    --output "../data/tmp/players.csv" \
    'https://www.toernooi.nl/organization/export/export_memberperroletypepergroup.aspx?id='"$PBO_OVL_ID"'&gid='"$PBO_OVL_GID"'&ft=1&glid=1'

curl \
    --silent \
    --location \
    --user-agent "$USER_AGENT" \
    --cookie "$COOKIES_PATH" \
    --cookie-jar "$COOKIES_PATH" \
    --output "../data/tmp/matches.csv" \
    'https://www.toernooi.nl/sport/admin/exportteammatches.aspx?id='"$PBO_COMPETITIE_ID"'&ft=1&sd='"$PBO_COMPETITIE_START_DAY"'000000&ed='"$PBO_COMPETITIE_END_DAY"'000000'

curl \
    --silent \
    --location \
    --user-agent "$USER_AGENT" \
    --cookie "$COOKIES_PATH" \
    --cookie-jar "$COOKIES_PATH" \
    --output "../data/tmp/locations.csv" \
    'https://toernooi.nl/sport/admin/exportlocations.aspx?id='"$PBO_COMPETITIE_ID"'&ft=1'
