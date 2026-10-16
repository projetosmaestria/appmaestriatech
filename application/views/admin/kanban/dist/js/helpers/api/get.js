async function api_get(endpoint, params) {
      const searchParams = new URLSearchParams();
      if (params) {
            for (const key in params) {
                  if (Array.isArray(params[key])) {
                        params[key].forEach(val => searchParams.append(key, val));
                  } else {
                        searchParams.append(key, params[key]);
                  }
            }
      }
      
      const response = await fetch(api_url + endpoint + '?' + searchParams.toString(), {
            method: "get",
            headers: headers
      });
      
      if (response.ok) {
            let json = await response.json();
            return json;
      } else {
            return `HTTP error: ${response.status}`;
      }
}

export { api_get };