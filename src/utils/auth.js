import { API_BASE_URL } from "../config";

export const auth = {
  loginMethod: "POST",
  logoutMethod: "GET",
  loginUrl: `${API_BASE_URL}/auth/login`,
  logoutUrl: `${API_BASE_URL}/auth/logout`,
  storage: window.localStorage,

  set accessToken(value) {
    this.saveInStorage("jwt", value);
  },

  get accessToken() {
    return this.getFromStorage("jwt");
  },

  set userRole(value) {
    this.saveInStorage("role", value);
  },

  get userRole() {
    return this.getFromStorage("role");
  },

  set districtId(id) {
    this.saveInStorage("districtId", id);
  },

  get districtId() {
    return this.getFromStorage("districtId");
  },

  async logIn(formData) {
    const response = await this.http(this.loginMethod, this.loginUrl, formData);

    if (!response.status) return Promise.reject("Bad request");

    const { jwt, role, districtId } = response.data;

    this.accessToken = jwt;
    this.userRole = role;
    this.districtId = districtId;
  },

  logOut() {
    this.purge();
    this.http(this.logoutMethod, this.logoutUrl);
  },

  getRole() {
    return Number(this.userRole);
  },

  getDistrictId() {
    return Number(this.districtId);
  },

  getAccessToken() {
    return String(this.accessToken);
  },

  purge() {
    this.accessToken = null;
    this.userRole = null;
    this.districtId = null;
  },

  isAuthorized() {
    return Boolean(this.accessToken);
  },

  async http(method, url, props) {
    let params = {
      method: method,
      mode: "cors",
      credentials: "omit",
      headers: {
        "Content-Type": "application/json",
      },
    };

    if (this.isAuthorized())
      params.headers.Authorization = `Bearer ${this.accessToken}`;

    if (props) params.body = JSON.stringify(props);

    const response = await fetch(url, params);

    if (response.status < 200 || response.status >= 300) {
      const error = new Error(`${response.status} ${response.statusText}`);
      error.config = { response };

      throw error;
    }

    if (url == this.loginUrl) return response.json();
  },

  saveInStorage(key, value) {
    value ? this.storage.setItem(key, value) : this.storage.removeItem(key);
  },

  getFromStorage(key) {
    return this.storage.getItem(key);
  },
};
