import axios from "axios";

const http = axios.create({
  baseURL: "http://localhost:8000/backend/", // এখানে আপনার Laravel এর root URL
  headers: {
    "X-Requested-With": "XMLHttpRequest",
    "Accept": "application/json",
  },
  timeout: 15000,
});

export default http;
