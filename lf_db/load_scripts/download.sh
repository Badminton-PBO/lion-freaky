PBO_USERNAME=''
PBO_PWD=''
if [ -r ./secret.cfg ] ; then
	. ./secret.cfg
fi
PBO_COMPETITIE_ID='EF6D253B-4410-4B4F-883D-48A61DDA350D'
PBO_COMPETITIE_START_DAY='20140801'
PBO_COMPETITIE_END_DAY='20150731'
PBO_OVL_ID='638D0B55-C39B-43AB-8A9D-D50D62017FBE'
PBO_OVL_GID='3825E3C5-1371-4FF6-94AF-C4A3B152802A'

USER_AGENT='Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2.11) Gecko/20101012 Firefox/3.6.11'
COOKIES_PATH='../data/tmp/cookie.txt'

rm ../data/tmp/*

curl \
    --silent \
    --location \
    --user-agent "$USER_AGENT" \
    --cookie-jar "$COOKIES_PATH" \
    --data '__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=UUiMxwEx9hHvimyioyqnDsPeGHVhftVJBPzvMyBm5nWccdiBywv3QDxgEVHimBhZLfFxnqMgLpiBa8CEKKa3x6Uhn0LDZHrEMQdVDfhSGLlzrzVwQnCCMgjIrrff1w%2Fns2ZbUOIqxYB%2BuyKbAcZX1yj1sTbXns%2FWneHaUeug74iw2Xhl%2BXeX%2BPsSZFtEDRn6g50dG%2FMSqWd69WRYyhOEgAy6Yit%2FHOph0ZJ%2BbAW%2FxlZjn370gsyD0w0sPQYsKLtSUQAddNs449CLeUxVmAoz62w3z6FS0Wo3SxN3IeVJ7CR7bdS0&__VIEWSTATEGENERATOR=625BA342&__EVENTVALIDATION=esjTYL6h5M6mOKWnx8EE9ZzO6FcwluwV6dQ6fO6I3XARrWQgvo3eJFvsbWtvibhiVdclIwNz85bH%2FmRomytK6rQZ%2F4eCGyogZvZIRGOi8SHThiDianeeT5xtK0p1F1Ohu%2FSzhOt6p11cJVZHV2qLM1c5iHs%2BYImLY2TMjUs%2FFGmUEreinSxoisZiGr5OgmYJOJOJrwDU8nPW4fEe%2FYsg%2B%2B2kT14i%2B56o4F7teXtsyQvIEBVrePxYhOncwTXw6XQf4962vg%3D%3D&tbxSearchQuery=Zoek...&ctl00%24ctl00%24ctl00%24cphPage%24ddlSearchType=1&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24UserName='"$PBO_USERNAME"'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24Password='"$PBO_PWD"'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24LoginButton=Inloggen' \
    --output '../data/tmp/login.txt' \
    'https://toernooi.nl/member/login.aspx'

curl \
    --silent \
    --location \
    --user-agent "$USER_AGENT" \
    --cookie "$COOKIES_PATH" \
    --cookie-jar "$COOKIES_PATH" \
    --output "../data/tmp/teams.csv" \
    'https://toernooi.nl/sport/admin/exportteams.aspx?id='"$PBO_COMPETITIE_ID"'&ft=1'

curl \
    --silent \
    --location \
    --user-agent "$USER_AGENT" \
    --cookie "$COOKIES_PATH" \
    --cookie-jar "$COOKIES_PATH" \
    --output "../data/tmp/clubs.csv" \
    'https://toernooi.nl/organization/export/group_subgroups_export.aspx?id='"$PBO_OVL_ID"'&gid='"$PBO_OVL_GID"'&ft=1'

curl \
    --silent \
    --location \
    --user-agent "$USER_AGENT" \
    --cookie "$COOKIES_PATH" \
    --cookie-jar "$COOKIES_PATH" \
    --output "../data/tmp/players.csv" \
    'https://toernooi.nl/organization/export/export_memberperroletypepergroup.aspx?id='"$PBO_OVL_ID"'&gid='"$PBO_OVL_GID"'&ft=1&glid=1'

curl \
    --silent \
    --location \
    --user-agent "$USER_AGENT" \
    --cookie "$COOKIES_PATH" \
    --cookie-jar "$COOKIES_PATH" \
    --output "../data/tmp/matches.csv" \
    'https://toernooi.nl/sport/admin/exportteammatches.aspx?id='"$PBO_COMPETITIE_ID"'&ft=1&sd='"$PBO_COMPETITIE_START_DAY"'000000&ed='"$PBO_COMPETITIE_END_DAY"'000000'
