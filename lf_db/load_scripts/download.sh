PBO_USERNAME=''
PBO_PWD=''
if [ -r ./secret.cfg ] ; then
	. ./secret.cfg
fi
PBO_COMPETITIE_ID='CDACB157-6AA4-4B4C-B4F8-A207D9D43190'
PBO_COMPETITIE_START_DAY='20190801'
PBO_COMPETITIE_END_DAY='20200731'
PBO_OVL_ID='638D0B55-C39B-43AB-8A9D-D50D62017FBE'
PBO_OVL_GID='3825E3C5-1371-4FF6-94AF-C4A3B152802A'

USER_AGENT='User-Agent=Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:48.0) Gecko/20100101 Firefox/48.0'
COOKIES_PATH='../data/tmp/cookie.txt'

rm ../data/tmp/*

# Setting cookie to bypass "do you accept cookies" permission
echo "#HttpOnly_www.toernooi.nl	FALSE	/	FALSE	0	st	c=1" >> $COOKIES_PATH

curl \
    --location \
    --user-agent "$USER_AGENT" \
    --cookie "$COOKIES_PATH" \
    --cookie-jar "$COOKIES_PATH" \
    --output '../data/tmp/login1.html' \
    'https://www.toernooi.nl/User/Login'

REQUEST_VERIFICATION_TOKEN=$(grep "<input name=\"__RequestVerificationToken" ../data/tmp/login1.html | sed -n 's/^.*value="\(.*\)".*$/\1/p')
echo $REQUEST_VERIFICATION_TOKEN

curl \
    --location \
    --user-agent "$USER_AGENT" \
    --cookie "$COOKIES_PATH" \
    --cookie-jar "$COOKIES_PATH" \
    --data '__RequestVerificationToken='"$REQUEST_VERIFICATION_TOKEN"'&Login='"$PBO_USERNAME"'&Password='"$PBO_PWD"'&ReturnUrl=%2F&ReturnUrlUnauthorized=' \
    --output '../data/tmp/login2.html' \
    'https://www.toernooi.nl/User/Login'

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
