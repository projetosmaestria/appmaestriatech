const api_url = "http://localhost:3000";
//const api_url = 'https://tubos.ngrok.dev';

const key_maps = "AIzaSyD_BJ_pQVjqKYfYTv2DOoI88dnwyau6qWs";

let headers = {
  Accept: "application/json",
  "Content-Type": "application/json",
  //'token-api-sax': "SfBWlLa5TsrnQehJYdVMRqBzm.gjXzywfHBSyqE0Wtobcr4FmqC", // Mith
  //'token-api-sax': "XdhkpgjzmHDVtnousJqlzvLvw.havfA7ByVdbc1f3ZDAGPt2l9v", // Line
  //'token-api-sax': "qNPRzWNseg5QqI8s5L7dRi35u.NxH3oitAbTkN1is7GmsYW3gOv", // Chavencomga
  //"token-api-sax": "4EGyjc0N4x6kGcJBGujKwDOaZ.wy4u76m35dIRjfTZuI31xnOxq", // Aguia
  //'token-api-sax' : "17e721a855359a664c490ebac.69afb23afc3ffcf250bc463d3", // Tagliari
  //'token-api-sax': "F6MduP3hkCucrvTIn1yMNxerN.4CoclzuMdwHqeEM99y98ZsEoF", // RJ Tubos
  //"token-api-sax": "MeTNqjBkQi4umebwGWxWDJOyY.HkFF8pDCsCtqy0TbqgCrhdW8l", // Amplisign Homologação
  "token-api-sax": "sbSWyGqkWpRa1gXyheRWEsrvr.jH3rKYmeduycckSTsAImftpA0", // dp
  "x-access-token": localStorage.getItem("x-access-token"),
  "Access-Control-Allow-Origin": "*",
  "Access-Control-Allow-Headers":
    "Origin, X-Request-Width, Content-Type, Accept",
};
