import axios from 'axios';

import { API_BASE_URL } from '../config';

/**
 * Single axios instance for the whole app. Centralizes base URL, timeout and
 * default headers so screens/hooks never deal with transport details.
 */
export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  timeout: 20000,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
});
