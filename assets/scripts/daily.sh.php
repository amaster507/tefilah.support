<?php
http_response_code(403);
die('Error: daily.sh.php is not meant to be executed, this file is an example of a shell script that generates a daily email and sends it using the Mailgun API.');
/**
 * INSTRUCTIONS:
 * 1. Copy this file to your server in a non-public directory. named daily.sh
 *    (remove the .php)
 * 2. Sign up for a free Mailgun account at https://www.mailgun.com/. And
 *    configure your domain to use Mailgun for sending emails, and get your API key.
 * 3. Replace the placeholder <your-api-key-here> with your Mailgun API key.
 * 4. Update paths to match your server replacing <your-[private|public]-path-here> with the correct path.
 * 4. Make sure the file is executable by running `chmod +x daily.sh`.
 * 6. Add a cron job to run this script daily.
 *   Example: `0 7 * * * /path/to/daily.sh >> /path/to/daily.log 2>&1`
 * 7. Be sure to remove this `<?php ... ?>` block from the file before running it.
 */
?>
#!/bin/bash
echo "$(date): Generating daily email..."
date=$(date +%m/%d/%Y)
dateLong=$(date "+%A, %B %d, %Y")
week=$(($(($(date +%s) - $(date -d "1970-01-05" +%s))) / 604800))
countries=$(cat <your-path-here>/assets/json/countries.json)
countriesCount=$(echo $countries | jq '. | length')
index=$(($week % $countriesCount))
country=$(echo $countries | jq ".[$index]")
countryName=$(echo $country | jq -r '.name.common')
fips=$(echo $country | jq -r '.fips')
countryData=$(curl -s "https://restcountries.com/v3.1/name/${countryName}" | jq '.')
countryFlag=$(echo $countryData | jq -r '.[0].flag')
coatOfArms=$(echo $countryData | jq -r '.[0].coatOfArms.svg')
languages=$(echo $countryData | jq -r '.[0].languages | join(", ")')
population=$(echo $countryData | jq -r '.[0].population')
area=$(echo $countryData | jq -r '.[0].area')
borders=$(echo $countryData | jq -r '.[0].borders | join(", ")')
capital=$(echo $countryData | jq -r '.[0].capital[0]')
if [ -z "$capital" ]; then
  capital=$(echo $countryData | jq -r '.[0].capital')
fi
officialName=$(echo $countryData | jq -r '.[0].name.official')
currency=$(echo $countryData | jq -r '.[0].currencies | map(.name) | join(", ")')
region=$(echo $countryData | jq -r '.[0].region')
subregion=$(echo $countryData | jq -r '.[0].subregion')
timezones=$(echo $countryData | jq -r '.[0].timezones | join(", ")')
googleMaps=$(echo $countryData | jq -r '.[0].maps.googleMaps')
wikipedia="https://wikipedia.org/wiki/${countryName}"
wikipediaHrefPolitics="https://wikipedia.org/wiki/${countryName}#Politics"
joshuaProjectUrl="https://joshuaproject.net/countries/${fips}"
dayOfWeek=$(date +%w)
daily=$(cat <your-public-path-here>/assets/json/daily.json | jq ".[$dayOfWeek]")
daily=$(echo $daily | sed "s|<span class='|@@|g")
daily=$(echo $daily | sed "s|'></span>|@@|g")
task=$(echo $daily | jq -r '.task')
focus=$(echo $daily | jq -r '.focus')
prayFor=$(echo $daily | jq -r '.prayers')
function buildPrayFor() {
  local items=$1 
  echo "<ul>"
  echo "$items" | jq -c '.[]' | while read item; do
    if [[ $item == \"* ]]; then
      echo "<li>${item:1:-1}</li>"
    else
      buildPrayFor "$item"
    fi
  done
  echo "</ul>"
}
prayFor=$(buildPrayFor "$prayFor")
prayFor=$(echo $prayFor | tr -d '\n')
prayForText=$(echo $prayFor | sed 's/<[^>]*>//g')
dailyTemplate=$(cat <your-private-path-here>/dailyTemplate.html)
dailyTemplate=$(echo $dailyTemplate | sed "s|@@DATA-FOCUS@@|$focus|gi")
dailyTemplate=$(echo $dailyTemplate | sed "s|@@DATA-TASK@@|$task|gi")
dailyTemplate=$(echo $dailyTemplate | sed "s|@@DATA-PRAY-FOR@@|$prayFor|gi")
dailyTemplate=$(echo $dailyTemplate | sed "s|@@DATA-DATE@@|$date|gi")
dailyTemplate=$(echo $dailyTemplate | sed "s|@@DATA-FULL-DATE@@|$dateLong|gi")
dailyTemplate=$(echo $dailyTemplate | sed "s|@@DATA-NAME@@|$countryName|gi")
focus=$(echo $focus | sed "s|@@DATA-NAME@@|$countryName|gi")
prayForText=$(echo $prayForText | sed "s|@@DATA-NAME@@|$countryName|gi")
dailyTemplate=$(echo $dailyTemplate | sed "s|@@DATA-FLAG@@|$countryFlag|gi")
dailyTemplate=$(echo $dailyTemplate | sed "s|@@DATA-COAT-OF-ARMS@@|$coatOfArms|gi")
dailyTemplate=$(echo $dailyTemplate | sed "s|@@DATA-LANGUAGES@@|$languages|gi")
prayForText=$(echo $prayForText | sed "s|@@DATA-LANGUAGES@@|$languages|gi")
dailyTemplate=$(echo $dailyTemplate | sed "s|@@DATA-POPULATION@@|$population|gi")
focus=$(echo $focus | sed "s|@@DATA-POPULATION@@|$population|gi")
prayForText=$(echo $prayForText | sed "s|@@DATA-POPULATION@@|$population|gi")
dailyTemplate=$(echo $dailyTemplate | sed "s|@@DATA-AREA@@|$area|gi")
prayForText=$(echo $prayForText | sed "s|@@DATA-AREA@@|$area|gi")
dailyTemplate=$(echo $dailyTemplate | sed "s|@@DATA-BORDERS@@|$borders|gi")
prayForText=$(echo $prayForText | sed "s|@@DATA-BORDERS@@|$borders|gi")
dailyTemplate=$(echo $dailyTemplate | sed "s|@@DATA-CAPITAL@@|$capital|gi")
prayForText=$(echo $prayForText | sed "s|@@DATA-CAPITAL@@|$capital|gi")
dailyTemplate=$(echo $dailyTemplate | sed "s|@@DATA-OFFICIAL-NAME@@|$officialName|gi")
prayForText=$(echo $prayForText | sed "s|@@DATA-OFFICIAL-NAME@@|$officialName|gi")
dailyTemplate=$(echo $dailyTemplate | sed "s|@@DATA-CURRENCY@@|$currency|gi")
prayForText=$(echo $prayForText | sed "s|@@DATA-CURRENCY@@|$currency|gi")
dailyTemplate=$(echo $dailyTemplate | sed "s|@@DATA-REGION@@|$region|gi")
prayForText=$(echo $prayForText | sed "s|@@DATA-REGION@@|$region|gi")
dailyTemplate=$(echo $dailyTemplate | sed "s|@@DATA-SUBREGION@@|$subregion|gi")
prayForText=$(echo $prayForText | sed "s|@@DATA-SUBREGION@@|$subregion|gi")
dailyTemplate=$(echo $dailyTemplate | sed "s|@@DATA-TIMEZONES@@|$timezones|gi")
prayForText=$(echo $prayForText | sed "s|@@DATA-TIMEZONES@@|$timezones|gi")
dailyTemplate=$(echo $dailyTemplate | sed "s|@@DATA-GOOGLE-MAPS@@|$googleMaps|gi")
prayForText=$(echo $prayForText | sed "s|@@DATA-GOOGLE-MAPS@@|$googleMaps|gi")
dailyTemplate=$(echo $dailyTemplate | sed "s|@@DATA-WIKIPEDIA@@|$wikipedia|gi")
prayForText=$(echo $prayForText | sed "s|@@DATA-WIKIPEDIA@@|$wikipedia|gi")
dailyTemplate=$(echo $dailyTemplate | sed "s|@@DATA-WIKIPEDIA-HREF-POLITICS@@|$wikipediaHrefPolitics|gi")
prayForText=$(echo $prayForText | sed "s|@@DATA-WIKIPEDIA-HREF-POLITICS@@|$wikipediaHrefPolitics|gi")
dailyTemplate=$(echo $dailyTemplate | sed "s|@@DATA-JOSHUA-PROJECT-URL@@|$joshuaProjectUrl|gi")
prayForText=$(echo $prayForText | sed "s|@@DATA-JOSHUA-PROJECT-URL@@|$joshuaProjectUrl|gi")
echo "$dailyTemplate" > <your-private-path-here>/today.html
echo "$(date): Sending daily email..."
response=$(curl -i -X POST \
    -u 'api:<your-api-key-here>' \
    'https://api.mailgun.net/v3/mg.pray.support/messages' \
    -H 'Content-Type: multipart/form-data' \
    -F from='Tefilah Support | Pray Support <no-reply@mg.pray.support>' \
    -F to='daily@mg.pray.support' \
    -F subject="$focus" \
    -F text="$prayForText" \
    -F html=@<your-private-path-here>/today.html)
echo "$response"
