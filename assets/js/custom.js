function getDate() {
    const params = new URLSearchParams(window.location.search);
    const dateParam = params.get('date');
    console.log('Date parameter:', dateParam);
    if (!dateParam) {
        return new Date();
    }
    try {
        const date = new Date(dateParam);
        date.setMinutes(date.getMinutes() + date.getTimezoneOffset());
        if (date.toString() === 'Invalid Date') {
            throw new Error('Invalid date');
        }
        console.log('Parsed date:', date);
        return date;
    } catch(e) {
        return new Date();
    }
}

const now = getDate();
console.log('Current date:', now);
const joshuaProjectApiKey = '8a9bb33bc663';

function getCurrentWeekNumber() {
    const start = new Date(1970, 0, 5);
    const diff = now - start + (start.getTimezoneOffset() - now.getTimezoneOffset()) * 60 * 1000;
    const oneWeek = 1000 * 60 * 60 * 24 * 7;
    return Math.floor(diff / oneWeek);
}

function getCountryForWeek(countries) {
    const week = getCurrentWeekNumber();
    console.log('Week number:', week);
    const index = week % countries.length;
    return countries[index];
}

function setData(className, data, prop = 'textContent') {
    var elements = document.getElementsByClassName(className);
    for (var i = 0; i < elements.length; i++) {
        elements[i][prop] = data;
    }
}

function formatDate(date) {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

function buildList(data) {
    const ul = document.createElement('ul');
    data.forEach(item => {
        if (Array.isArray(item)) {
            ul.appendChild(buildList(item));
        } else {
            const li = document.createElement('li');
            li.innerHTML = item;
            ul.appendChild(li);
        }
    });
    return ul;
}

function getCurrentTimeInTimeZone(timeZone) {
    const offset = timeZone.replace('UTC', '');
    const options = {
        timeZone: offset,
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    };
    return new Intl.DateTimeFormat('en-US', options).format(new Date());
}

fetch('assets/json/daily.json').then(response => response.json()).then(data => {
    var day = now.getDay();
    setData('data-fulldate', formatDate(now));
    setData('data-task', data[day].task, 'innerHTML');
    setData('data-focus', data[day].focus, 'innerHTML');
    const prayers = buildList(data[day].prayers);
    document.getElementById('pray-for').appendChild(prayers);
    getCountry();
})

function kmToMiles(km) {
    return '~'+Math.round(km * 0.621371);
}

function getCountry() {
    fetch('assets/json/countries.json')
    .then(response => response.json())
    .then(countries => {
        const country = getCountryForWeek(countries);
        console.log('Country for this week:', country);
        // Set the country data in the HTML
        setData('data-name', country.name.common);
        return country;
    }).then((info) => {
        const country = info.name.common;
        const fips = info.fips;
        fetch(`https://restcountries.com/v3.1/name/${country}`).then(response => response.json()).then(data => {
            if (Array.isArray(data)) {
                if (data.length > 0) {
                    countryData = data[0];
                    setData('data-flag', countryData.flag);
                    document.getElementById('bg').style.backgroundImage = `url('${countryData.coatOfArms.svg}')`;
                    document.getElementById('countryFlag').src = countryData.flags.png;
                    const languages = Object.values(countryData.languages).join(', ');
                    setData('data-languages', languages);
                    setData('data-population', countryData.population.toLocaleString());
                    setData('data-area', countryData.area.toLocaleString()+' km²/'+kmToMiles(countryData.area).toLocaleString()+' mi²');
                    const borders = Array.isArray(countryData.borders) ? countryData.borders.join(', ') : 'None';
                    setData('data-borders', borders);
                    setData('data-capital', countryData.capital);
                    setData('data-official-name', countryData.name.official);
                    setData('data-currency', Object.values(countryData.currencies).map(c => c.name).join(', '));
                    setData('data-region', countryData.region);
                    setData('data-subregion', countryData.subregion);
                    setData('data-timezones', countryData.timezones.map(tz => getCurrentTimeInTimeZone(tz)).join(', '));
                    const googleMapsLink = countryData.maps.googleMaps;
                    setData('data-google-maps', googleMapsLink);
                    setData('data-google-maps', googleMapsLink, 'href');
                    const wikipediaLink = `https://wikipedia.org/wiki/${countryData.name.common}`;
                    setData('data-wikipedia', wikipediaLink);
                    setData('data-wikipedia-href', wikipediaLink, 'href');
                    setData('data-wikipedia-href-politics', wikipediaLink+'#Politics', 'href');
                    const joshuaProjectLink = `https://joshuaproject.net/countries/${fips}`;
                    setData('data-joshua-project-url', joshuaProjectLink);
                    setData('data-joshua-project-href', joshuaProjectLink, 'href');

                } else {
                    throw new Error('Data is an empty array.');
                }
            } else {
                throw new Error('Data is not an array.');
            }

        }).catch(error => console.error('Error fetching the JSON data: ', error));
    })
    .catch(error => console.error('Error fetching the countries data:', error));
}

const form = document.getElementById('ContactForm');
const postContactDiv = document.getElementById('PostContact');
form.addEventListener('submit', async (e) => {
    e.preventDefault();

    // hide the form
    form.style.display = 'none';
    // add a message to show that the contact form is being sent
    postContactDiv.innerHTML = '<p>Sending...</p>';
    // show the post contact message
    postContactDiv.style.display = 'block';

    // get the form data into a URLSearchParams object
    const formData = new URLSearchParams(new FormData(form));

    const response = await fetch(form.action, {
        method: form.method,
        body: formData,
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    });

    if (response.ok) {
        // show the success message
        postContactDiv.innerHTML = '<p>Thank you! Your message was sent. Please allow 2-3 business days for a reply if merited.</p>';
    } else {
        // show the error message
        postContactDiv.innerHTML = '<p>There was an error sending your message. Please try again later</p>';
    }
})

const subscribeForm = document.getElementById('SubscribeForm');
const postSubscribeDiv = document.getElementById('PostSubscribe');
subscribeForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    // hide the form
    subscribeForm.style.display = 'none';
    // show the post contact message
    postSubscribeDiv.style.display = 'block';

    // get the form data into a URLSearchParams object
    const formData = new URLSearchParams(new FormData(subscribeForm));

    const response = await fetch(subscribeForm.action, {
        method: subscribeForm.method,
        body: formData,
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    });

    if (response.ok) {
        // show the success message
        postSubscribeDiv.innerHTML = '<p>Thank you for subscribing!</p>';
    } else {
        // show the error message
        postSubscribeDiv.innerHTML = '<p>There was an error subscribing. Please try again later</p>';
    }
})

const unsubscribeForm = document.getElementById('UnsubscribeForm');
const postUnsubscribeDiv = document.getElementById('PostUnsubscribe');
unsubscribeForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    // hide the form
    unsubscribeForm.style.display = 'none';
    // show the post contact message
    postUnsubscribeDiv.style.display = 'block';

    // get the form data into a URLSearchParams object
    const formData = new URLSearchParams(new FormData(unsubscribeForm));

    const response = await fetch(unsubscribeForm.action, {
        method: unsubscribeForm.method,
        body: formData,
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    });

    if (response.ok) {
        // show the success message
        postUnsubscribeDiv.innerHTML = '<p>Sorry to see you go! Hope we did not bother you too much trying to initiate genuine prayer from the heart for World Missions!</p>';
    } else {
        // show the error message
        postUnsubscribeDiv.innerHTML = '<p>There was an error unsubscribing. Please try again later</p>';
    }
})