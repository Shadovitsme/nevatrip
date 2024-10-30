import axios from 'axios';
import jquery from "jquery";
window.axios = axios;
window.$ = jquery;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";