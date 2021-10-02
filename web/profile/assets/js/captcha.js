document.querySelector('form').addEventListener('submit', (e) => {
    e.preventDefault();
    grecaptcha.ready(function() {
        grecaptcha.execute('6LcrNJ4cAAAAAKIYPhVuc4CwnmSkap-9L_MTNvS2', {action: 'homepage'}).then(function(token) {
            document.getElementById('token').value = token;
            const data = new URLSearchParams();
			for (const pair of new FormData(document.querySelector('form'))) {
				data.append(pair[0], pair[1]);
			}
            fetch('assets/php/captcha.php', {
                method: 'post',
                body: data,
            })
            .then(response => response.json())
            .then(result => {
                if (result['om_score'] >= 0.5) {
                    return om_score = true;
                } else {
                    return om_score = false;
                }
            });
        });
    });
});