var markersArray = [];
// Esta função vai percorrer a informação contida na variável markersData
// e cria os marcadores através da função createMarker
function displayMarkers(){
   // esta variável vai definir a área de mapa a abranger e o nível do zoom
   // de acordo com as posições dos marcadores
   var bounds = new google.maps.LatLngBounds();

   // Loop que vai percorrer a informação contida em markersData 
   // para que a função createMarker possa criar os marcadores 
   console.log(markersData);
   for (var i = 0; i < markersData.length; i++){

      var latlng = new google.maps.LatLng(markersData[i].lat, markersData[i].lng);
      var nome = markersData[i].nome;
      var morada1 = markersData[i].morada1;
      var morada2 = markersData[i].morada2;
      var codPostal = markersData[i].codPostal;
      var email = markersData[i].email;
      var telefone = markersData[i].celular;

      createMarker(latlng, nome, morada1, morada2, codPostal, email, telefone);

      // Os valores de latitude e longitude do marcador são adicionados à
      // variável bounds
      bounds.extend(latlng); 
   }

   // Depois de criados todos os marcadores,
   // a API, através da sua função fitBounds, vai redefinir o nível do zoom
   // e consequentemente a área do mapa abrangida de acordo com
   // as posições dos marcadores
   map.fitBounds(bounds);
}

// Função que cria os marcadores e define o conteúdo de cada Info Window.
function createMarker(latlng, nome, morada1, morada2, codPostal, email, telefone){
   var marker = new google.maps.Marker({
      map: map,
      position: latlng,
      title: nome
   });

   markersArray.push(marker);

   // Evento que dá instrução à API para estar alerta ao click no marcador.
   // Define o conteúdo e abre a Info Window.
   google.maps.event.addListener(marker, 'click', function() {
      
      // Variável que define a estrutura do HTML a inserir na Info Window.
      if(morada1 != "" && morada2 != "") {
         var iwContent = '<div id="iw_container">' +
         '<div class="iw_title">' + nome + '</div>' +
         '<div class="iw_content">' + morada1 + '<br />' +
         morada2 + '<br />' +
         codPostal + '<br />' +
         email + '<br />' +
         telefone + '</div></div>';
      } else {
         var iwContent = '<div id="iw_container">' +
         '<div class="iw_title">' + nome + '</div>' +
         '<div class="iw_content">' + email + '<br />' +
         telefone + '</div></div>';
      }
      
      // O conteúdo da variável iwContent é inserido na Info Window.
      infoWindow.setContent(iwContent);

      // A Info Window é aberta com um click no marcador.
      infoWindow.open(map, marker);
   });
}

function initialize() {
   var mapOptions = {
      center: new google.maps.LatLng(-15.4048109,-47.8386378),
      zoom: 9,
      mapTypeId: 'roadmap',
   };

   map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

   // Cria a nova Info Window com referência à variável infoWindow.
   // O conteúdo da Info Window é criado na função createMarker.
   infoWindow = new google.maps.InfoWindow();

   // Evento que fecha a infoWindow com click no mapa.
   google.maps.event.addListener(map, 'click', function() {
      infoWindow.close();
   });

   // Chamada para a função que vai percorrer a informação
   // contida na variável markersData e criar os marcadores a mostrar no mapa
   displayMarkers();
}

function setMapOnAll(map) {
  for (var i = 0; i < markersArray.length; i++) {
    markersArray[i].setMap(map);
  }
}

function clearMarkers() {
  setMapOnAll(null);
}

function deleteMarkers() {
  clearMarkers();
  markersArray = [];
}

google.maps.event.addDomListener(window, 'load', initialize);