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

# Setting cookie to bypass "do you accept cookies" permission
echo "#HttpOnly_toernooi.nl	FALSE	/	FALSE	0	st	c=1" >> $COOKIES_PATH

curl \
    --location \
    --user-agent "$USER_AGENT" \
    --cookie "$COOKIES_PATH" \
    --cookie-jar "$COOKIES_PATH" \
    --data '__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=bVJyNakpPV0f4usZzwuN0RFVXjxNPOBoGtK6FEtcdTPSG444DYCeMxjPP%2BiPDCaICHQ%2F1dZqZeZzeXeywJGGihN0%2Fp8gYzn6OTPl87P4FkPqL58X95uuuiVhxEQX4%2FJWFtdYbgx2D9OTVR0bC5jkB%2FVU116v4UcjRXIqUnmyLwgbSmnnBKWyv6ozI718LKmcj7rg3HPgGbq8Yikllj2288lkzCDxVAJuS9MP%2BbWpLQere1BO8qdTyI10Kh8xT%2FbYdW1DJ2PIKaKG08Hho%2F8ynCX1tyfPnrXkp%2BSY96qgh5749ag3&__VIEWSTATEGENERATOR=625BA342&__EVENTVALIDATION=J835wQEgbd7lzA11VgzazffOVk1r%2BXIF%2Bmh7%2FskUlhLI18g6BjbRZrR39RELNs8I4ZS%2BV3Pg7rIB9%2FyiS%2FhudynPEygQu74u0hjhQja2FjJ5FoZPp6ItBWcVl4t4UY%2B88JU4j%2BXvrzja8NzwUIa%2BeUPiAsWG9G3pbdtKmEDVoxQnULUdmeGnRZeirmYN%2Fl7zAQ1jzO%2B73Z47Y%2BdZhP15McNE99cMoyoas8wCWpypwsyB%2F3nIghBD2sZQKVk5hKQUcR7PwQ%3D%3D&tbxSearchQuery=Zoek...&ctl00%24ctl00%24ctl00%24cphPage%24ddlSearchType=1&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24UserName='"$PBO_USERNAME"'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24Password='"$PBO_PWD"'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24LoginButton=Inloggen' \
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
