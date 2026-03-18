import axios from "axios";

const api = axios.create({
  baseURL: "http://localhost/Projects/task-manager-app/backend/api/v1",
});

export default api;
