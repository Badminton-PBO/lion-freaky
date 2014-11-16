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
	--data '__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwUJNjM0MDYzNDI3ZBgBBR5fX0NvbnRyb2xzUmVxdWlyZVBvc3RCYWNrS2V5X18WAgU9Y3RsMDAkY3RsMDAkY3RsMDAkY3BoUGFnZSRjcGhQYWdlJGNwaFBhZ2UkcG5sTG9naW4kUmVtZW1iZXJNZQVDY3RsMDAkY3RsMDAkY3RsMDAkY3BoUGFnZSRjcGhQYWdlJGNwaFBhZ2UkcG5sTG9naW4kTG9naW5JbWFnZUJ1dHRvbkDFcxzmupMNoFNI2833VjIpspSb&__VIEWSTATEGENERATOR=625BA342&__EVENTVALIDATION=%2FwEdAAkkrxhVeFemLbIU82wv5PSCDc%2F5voaaGYfFlFBXi9EGFfyHSpCYj%2BAUNp9bXc20Z5f%2BOtme7httab8IViMP3HjzlRR%2BDpTMHdYiODpnuHxziR2B%2BiwIwJ5fF61AnAcX2%2BwvDdLMdOmJdT7lzlyuo8NCBjrAGg4uwJH4J35FqmwaB97lIlcv0kHWlCdwWozE4w6e5YuDNp%2F7v5Hoe%2Fq7l8Xai2IOSg%3D%3D&tbxSearchQuery=Zoek...&ctl00%24ctl00%24ctl00%24cphPage%24ddlSearchType=1&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24UserName='"$PBO_USERNAME"'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24Password='"$PBO_PWD"'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24LoginButton=Inloggen' \
    --output '../data/tmp/login.txt' \
    'http://toernooi.nl/member/login.aspx'

curl \
    --silent \
    --location \
    --user-agent "$USER_AGENT" \
    --cookie "$COOKIES_PATH" \
    --cookie-jar "$COOKIES_PATH" \
    --output "../data/tmp/clubs.csv" \
    'http://toernooi.nl/sport/admin/exportclubs.aspx?id='"$PBO_COMPETITIE_ID"'&ft=1'

curl \
    --silent \
    --location \
    --user-agent "$USER_AGENT" \
    --cookie "$COOKIES_PATH" \
    --cookie-jar "$COOKIES_PATH" \
    --output "../data/tmp/teams.csv" \
    'http://toernooi.nl/sport/admin/exportteams.aspx?id='"$PBO_COMPETITIE_ID"'&ft=1'

curl \
    --silent \
    --location \
    --user-agent "$USER_AGENT" \
    --cookie "$COOKIES_PATH" \
    --cookie-jar "$COOKIES_PATH" \
    --output "../data/tmp/players.csv" \
    'http://toernooi.nl/organization/export/export_memberperroletypepergroup.aspx?id='"$PBO_OVL_ID"'&gid='"$PBO_OVL_GID"'&ft=1&glid=1'

curl \
    --silent \
    --location \
    --user-agent "$USER_AGENT" \
    --cookie "$COOKIES_PATH" \
    --cookie-jar "$COOKIES_PATH" \
    --output "../data/tmp/matches.csv" \
    'http://toernooi.nl/sport/admin/exportteammatches.aspx?id='"$PBO_COMPETITIE_ID"'&ft=1&sd='"$PBO_COMPETITIE_START_DAY"'000000&ed='"$PBO_COMPETITIE_END_DAY"'000000'
