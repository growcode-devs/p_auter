$(document).ready(function () {
  fetchTopTracks();
});

function fetchTopTracks() {
  $.ajax({
    // url: "http://localhost/PruebaPHPmailer/get_tracks.php",
    url: "http://localhost/growcode/web_austero/php/api_data/get_top_ten.php",
    // url: "../get_tracks.php",
    method: "GET",
    dataType: "json",
    success: function (response) {
      // console.log("AJAX Success Response:", response);
      if (response.tracks) {
        displayTopTracks(response.tracks);
      } else {
        console.error("No tracks found in the response");
      }
    },
    error: function (err) {
      console.log("AJAX request failed:", err);
    },
  });
}

function displayTopTracks(tracks) {
  tracks.forEach((track, index) => {
    if (index < 8) {
      // Limiting to 8 tracks
      const trackName = track.name;
      const albumImage = track.album_image; // Use the first image
      const previewUrl = track.file_path;
      const spotifyUrl = track.url;
      const youtubeUrl = "https://www.youtube.com/@proyectoaustero"; // Band's YouTube link

      const card = `
      <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="player" style="position: relative;">
          <!-- Album Image -->
          <img src="${albumImage}" alt="${trackName}" class="album-image" />
          <!-- Song Information -->
          <div class="info">
            <h1>${trackName}</h1>
          </div>
          <!-- Track Time Progress Bar -->
          <div class="track-time">
            <div class="track">
              <div class="progress-fill" id="progress-bar-${index}"></div>
            </div>
          </div>
          <!-- Action buttons (Spotify, Play, YouTube) -->
          <div class="action-buttons">
            <a href="${spotifyUrl}" target="_blank" class="spotify-button">
              <i class="fab fa-spotify"></i>
            </a>
            <div class="play-button" id="play-pause-button-${index}" onclick="playPreview('${previewUrl}', ${index})">
              <i class="fa-solid fa-play"></i>
            </div>
            <a href="${youtubeUrl}" target="_blank" class="youtube-button">
              <i class="fab fa-youtube"></i>
            </a>
          </div>
        </div>
      </div>
    `;

      $("#track-cards-container").append(card);
    }
  });
}

let currentAudio = null;
let progressInterval = null;
let isPlaying = false;
let currentTrackIndex = null;

function playPreview(previewUrl, index) {
  const playButton = document.getElementById(`play-pause-button-${index}`);
  const progressBar = document.getElementById(`progress-bar-${index}`);

  // If this track is already playing, pause it
  if (currentTrackIndex === index && currentAudio && isPlaying) {
    currentAudio.pause(); // Pause the track
    isPlaying = false;
    playButton.innerHTML = '<i class="fa-solid fa-play"></i>'; // Change to play icon
    clearInterval(progressInterval); // Stop progress bar update
    return;
  }

  // If this track is paused, resume it
  if (currentTrackIndex === index && currentAudio && !isPlaying) {
    currentAudio.play(); // Resume the track
    isPlaying = true;
    playButton.innerHTML = '<i class="fa-solid fa-pause"></i>'; // Change to pause icon
    startProgressBar(progressBar); // Resume progress bar
    return;
  }

  // If switching to a new track, reset the previous one
  if (currentAudio) {
    currentAudio.pause(); // Pause the current audio
    clearInterval(progressInterval); // Clear the previous progress interval
    const previousButton = document.getElementById(
      `play-pause-button-${currentTrackIndex}`
    );
    const previousProgressBar = document.getElementById(
      `progress-bar-${currentTrackIndex}`
    );
    if (previousButton)
      previousButton.innerHTML = '<i class="fa-solid fa-play"></i>'; // Reset previous button icon
    if (previousProgressBar) previousProgressBar.style.width = "0%"; // Reset previous progress bar
  }

  // Start playing a new track
  currentAudio = new Audio(previewUrl);
  currentAudio.play();
  isPlaying = true;
  currentTrackIndex = index; // Update current track index

  playButton.innerHTML = '<i class="fa-solid fa-pause"></i>'; // Change to pause icon

  startProgressBar(progressBar); // Start the progress bar

  // Automatically stop the audio after 20 seconds
  const duration = 20; // Define the duration as 20 seconds
  currentAudio.currentTime = 0; // Ensure it starts from the beginning
  const stopPlayback = setTimeout(() => {
    if (currentAudio) {
      currentAudio.pause(); // Stop the audio after 20 seconds
      currentAudio.currentTime = 0; // Reset the track to the beginning
      clearInterval(progressInterval); // Stop the progress bar updates
      progressBar.style.width = "0%"; // Reset progress bar after song ends
      playButton.innerHTML = '<i class="fa-solid fa-play"></i>'; // Reset to play icon
      isPlaying = false; // Set playing status to false
    }
  }, duration * 1000); // Stop the audio after 20 seconds (20 * 1000ms)

  currentAudio.addEventListener("ended", () => {
    clearTimeout(stopPlayback); // Clear timeout in case the song ends earlier
    clearInterval(progressInterval); // Stop progress bar updates
    progressBar.style.width = "0%"; // Reset progress bar
    playButton.innerHTML = '<i class="fa-solid fa-play"></i>'; // Reset play icon
    isPlaying = false;
  });
}

// Function to handle progress bar updates
function startProgressBar(progressBar) {
  const duration = 20; // Preview duration is 20 seconds
  const step = 100 / (duration * 10); // Calculate the percentage step per 100ms
  let progress = (currentAudio.currentTime / duration) * 100; // Calculate initial progress

  progressInterval = setInterval(() => {
    if (isPlaying) {
      progress = (currentAudio.currentTime / duration) * 100;
      if (progress <= 100) {
        progressBar.style.width = `${progress}%`;
      } else {
        clearInterval(progressInterval); // Stop when 100% is reached
      }
    }
  }, 100); // Update progress every 100ms
}

// Contact-form

//Para mostrar el mensaje en el modal
function formResponse(head, body, loader) {
  const messageHead = document.querySelector("#form-response h6");
  const messageBody = document.querySelector("#form-response p");
  const resMessage = document.getElementById("form-response");
  loader.classList.add("d-none");
  resMessage.classList.remove("d-none");
  messageHead.innerHTML = head;
  messageBody.innerHTML = body;
}
//salir del modal
const resButton = document.querySelector("#form-response button");
resButton.addEventListener("click", () => {
  const resView = document.getElementById("form-loader");
  resView.classList.add("d-none");
});

//Enviar el mensaje
const contactForm = document.getElementById("contact-form");
contactForm.addEventListener("submit", function (e) {
  e.preventDefault();

  const resView = document.getElementById("form-loader");
  const loader = document.getElementById("img-form-loader");
  resView.classList.remove("d-none");
  loader.classList.remove("d-none");

  const formData = new FormData(contactForm);
  //cambiar ruta para deploy
  fetch("https://proyectoaustero.com/send_mail.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) {
        formResponse("Hubo un error", "Por favor inténtelo más tarde", loader);
        console.log("ma");
        console.log(response);
      } else {
        return response.json();
      }
    })
    .then((data) => {
      if (data.message) {
        formResponse(
          data.message,
          "Nos contactaremos contigo lo antes posible.",
          loader
        );
        contactForm.reset();
      }
    })
    .catch((error) => {
      formResponse("Hubo un error", "Por favor inténtelo más tarde", loader);
      console.error("Error:", error);
    });
});
// Contact-form end

// Back top
const biography = document.getElementById("discografia");
const arrow = document.getElementById("back-top");
window.addEventListener("scroll", function () {
  if (biography.offsetTop - 100 <= this.scrollY) {
    arrow.classList.remove("fade");
    arrow.classList.add("show");
  } else {
    arrow.classList.remove("show");
    arrow.classList.add("fade");
  }
});

arrow.addEventListener("click", function () {
  window.scrollTo({
    top: 0,
    behavior: "smooth",
  });
});

//ModalDev
function setModalView(overflow, zindex, state) {
  document.body.style.overflow = overflow;
  modalDev.style.zIndex = zindex;
  state == true
    ? modalDev.classList.add("show")
    : modalDev.classList.remove("show");
}
const btnDev = document.getElementById("btn-dev");
const modalDev = document.getElementById("modal-gatitos");
const btnClose = document.getElementById("closeM");
const modalCard = document.getElementById("dev-card");
btnDev.addEventListener("click", function () {
  setModalView("hidden", 100, true);
});
btnClose.addEventListener("click", function () {
  setModalView("auto", -1, false);
});
modalDev.addEventListener("click", function (event) {
  if (!modalCard.contains(event.target)) {
    setModalView("auto", -1, false);
  }
});

// Check if element is in the viewport
function isElementInViewport(el) {
  const rect = el.getBoundingClientRect();
  return (
    rect.top >= 0 &&
    rect.left >= 0 &&
    rect.bottom <=
      (window.innerHeight || document.documentElement.clientHeight) &&
    rect.right <= (window.innerWidth || document.documentElement.clientWidth)
  );
}

// Trigger the animation on scroll
$(document).ready(function () {
  // Only apply on desktop (PC) screens
  if ($(window).width() > 768) {
    // On scroll, check if each .player and .section-discografia is in view
    $(window).on("scroll", function () {
      $(".player").each(function () {
        if (isElementInViewport(this)) {
          $(this).addClass("show"); // Add the show class when in view
        }
      });
      // Check if .section-discografia is in view
      if (isElementInViewport($(".section-discografia")[0])) {
        $(".section-discografia").addClass("show");
      }
    });

    // Initial check in case elements are already in view
    $(window).trigger("scroll");
  }
});

// Trigger the animation on scroll
$(document).ready(function () {
  // Only apply on desktop (PC) screens
  if ($(window).width() > 768) {
    // On scroll, check if each .player is in view
    $(window).on("scroll", function () {
      $(".player").each(function () {
        if (isElementInViewport(this)) {
          $(this).addClass("show"); // Add the show class when in view
        }
      });
    });

    // Initial check in case elements are already in view
    $(window).trigger("scroll");
  }
});

$(document).ready(function () {
  // Only apply animations on desktop screens
  if ($(window).width() > 768) {
    // Adjusted function to trigger animation much earlier
    function isElementInViewport(el, offset = 300) {
      const rect = el.getBoundingClientRect();
      return (
        rect.top >= -offset &&
        rect.left >= 0 &&
        rect.bottom <=
          (window.innerHeight || document.documentElement.clientHeight) +
            offset &&
        rect.right <=
          (window.innerWidth || document.documentElement.clientWidth)
      );
    }

    // On scroll, check if each relevant element is in view
    $(window).on("scroll", function () {
      $(".player").each(function () {
        if (isElementInViewport(this, 300)) {
          $(this).addClass("show"); // Add the show class when in view
        }
      });

      // Animate .section-discografia title with higher offset
      if (isElementInViewport($(".section-discografia")[0], 300)) {
        $(".section-discografia").addClass("show");
      }

      // Animate #biografia title with higher offset
      if (isElementInViewport($("#biografia .titulo")[0], 300)) {
        $("#biografia .titulo").addClass("show");
      }

      // Animate #biografia image and text with higher offset
      if (isElementInViewport($("#biografia #imagen-bio")[0], 300)) {
        $("#biografia #imagen-bio").addClass("show");
      }
      if (isElementInViewport($("#biografia #texto-bio")[0], 300)) {
        $("#biografia #texto-bio").addClass("show");
      }
    });

    // Initial check in case elements are already in view
    $(window).trigger("scroll");
  }
});
