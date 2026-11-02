import {api} from './axios';

export const getPost = () => {
    return api.get('/messages');
};

export const storePost = (data) => {
    return api.post('/messages', data);
};