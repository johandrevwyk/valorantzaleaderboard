console.clear();

// Function to fetch leaderboard data from the server
function fetchLeaderboardData() {
    fetch('fetch_leaderboard.php')
        .then(response => response.json())
        .then(data => {
            renderLeaderboard(data);
        })
        .catch(error => console.error('Error fetching leaderboard data: ', error));
}

// Function to render the leaderboard with the fetched data
function renderLeaderboard(data) {
    const list = document.getElementById('list');
    
    // Clear the existing leaderboard
    list.innerHTML = '';
    
    // Add the header row
    let headerRow = document.createElement('li');
    headerRow.classList = 'c-list__item';
    headerRow.innerHTML = `
        <div class="c-list__grid">
            <div class="u-text--left u-text--small u-text--medium">Rank</div>
            <div class="u-text--left u-text--small u-text--medium">Player</div>
            <div class="u-text--right u-text--small u-text--medium">RR</div>
        </div>
    `;
    list.appendChild(headerRow);
    
    // Iterate over the fetched data and create leaderboard rows
    data.forEach((member, index) => {
    let newRow = document.createElement('li');
    newRow.classList = 'c-list__item';
    
    // Split the name and tag
    const [namePart, tagPart] = member.name.split('#');

    newRow.innerHTML = `
        <div class="c-list__grid">
            <div class="c-flag c-place u-bg--transparent">${index + 1}</div>
            <div class="c-media">
                <img class="c-avatar c-media__img" src="ranks/${member.rank.replace(/ /g, '_')}_Rank.png" />
                <div class="c-media__content">
                    <div class="c-media__title">
                        <span class="c-media__name">${namePart}</span>
                        <span class="c-media__tag">#${tagPart}</span>
                    </div>
                    <a class="c-media__link u-text--small" href="https://tracker.gg/valorant/profile/riot/${encodeURIComponent(member.name)}/overview" target="_blank">${member.rank}</a>
                </div>
            </div>
            <div class="u-text--right c-rr">
                <div class="u-mt--8">
                    <strong>${member.rr}</strong>
                </div>
            </div>
        </div>
    `;

    newRow.querySelector('.c-media__title').addEventListener('mouseenter', () => {
        newRow.querySelector('.c-media__tag').classList.add('visible');
    });

    newRow.querySelector('.c-media__title').addEventListener('mouseleave', () => {
        newRow.querySelector('.c-media__tag').classList.remove('visible');
    });
        
        // Check if the index is less than 3 (ranks 1-3)
        if (index < 3) {
            newRow.querySelector('.c-place').classList.add('u-text--dark');
            if (index === 0) {
                newRow.querySelector('.c-place').classList.add('u-bg--yellow');
                newRow.querySelector('.c-rr').classList.add('u-text--yellow');
            } else if (index === 1) {
                newRow.querySelector('.c-place').classList.add('u-bg--teal');
                newRow.querySelector('.c-rr').classList.add('u-text--teal');
            } else if (index === 2) {
                newRow.querySelector('.c-place').classList.add('u-bg--orange');
                newRow.querySelector('.c-rr').classList.add('u-text--orange');
            }
        }
        
        list.appendChild(newRow);
    });
}

// Call the fetchLeaderboardData function to load the leaderboard on page load
fetchLeaderboardData();

