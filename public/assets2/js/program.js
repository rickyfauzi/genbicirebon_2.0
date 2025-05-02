const search = document.querySelector(".search-box input"),
      images = document.querySelectorAll(".news-card");

search.addEventListener("keyup", e =>{
    if(e.key == "Enter"){
        let searcValue = search.value,
            value = searcValue.toLowerCase();
            images.forEach(image =>{
                if(value === image.dataset.name){ 
                    return image.style.display = "block";
                }
                image.style.display = "none";
            });
    }
});

search.addEventListener("keyup", () =>{
    if(search.value != "") return;

    images.forEach(image =>{
        image.style.display = "block";
    })
})

function addHoverClass(event) {
    event.target.classList.add('hovered');
  }
  
  function removeHoverClass(event) {
    event.target.classList.remove('hovered');
  }
  
  const newsCards = document.querySelectorAll('.news-card');
  
  newsCards.forEach((card) => {
    card.addEventListener('click', addHoverClass);
    card.addEventListener('blur', removeHoverClass);
  });
  