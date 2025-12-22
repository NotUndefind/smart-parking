const API_BASE_URL = 'http://localhost:8000/api';

// Fonction pour décoder le JWT (sans vérification de signature, juste pour lire les données)
function decodeJWT(token) {
    try {
        const base64Url = token.split('.')[1];
        const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));
        return JSON.parse(jsonPayload);
    } catch (error) {
        console.error('Erreur décodage JWT:', error);
        return null;
    }
}

async function apiCall(endpoint, method = 'GET', body = null) {
    const token = localStorage.getItem('token');
    const headers = {
        'Content-Type': 'application/json',
    };

    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const options = {
        method,
        headers,
    };

    if (body) {
        options.body = JSON.stringify(body);
    }

    try {
        const response = await fetch(`${API_BASE_URL}${endpoint}`, options);
        
        // Vérifier si la réponse est du JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error(`Réponse non-JSON: ${text.substring(0, 100)}`);
        }
        
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || `Erreur API (${response.status})`);
        }

        return data;
    } catch (error) {
        console.error('Erreur API:', error);
        throw error;
    }
}

const authAPI = {
    async login(email, password) {
        const data = await apiCall('/auth/login', 'POST', { email, password });
        if (data.token) {
            localStorage.setItem('token', data.token);
            // Décoder le JWT pour récupérer les infos de l'utilisateur
            const payload = decodeJWT(data.token);
            if (payload) {
                const userInfo = {
                    id: payload.sub,
                    email: payload.email,
                    first_name: payload.first_name,
                    last_name: payload.last_name
                };
                localStorage.setItem('user', JSON.stringify(userInfo));
            }
        }
        return data;
    },

    async register(userData) {
        return await apiCall('/auth/register', 'POST', userData);
    },

    async ownerLogin(email, password) {
        const data = await apiCall('/owner/login', 'POST', { email, password });
        if (data.token) {
            localStorage.setItem('token', data.token);
            // Décoder le JWT pour récupérer les infos de l'owner
            const payload = decodeJWT(data.token);
            if (payload) {
                const ownerInfo = {
                    id: payload.sub,
                    email: payload.email,
                    first_name: payload.first_name,
                    last_name: payload.last_name,
                    company_name: payload.company_name
                };
                localStorage.setItem('owner', JSON.stringify(ownerInfo));
            }
        }
        return data;
    },

    async ownerRegister(ownerData) {
        return await apiCall('/owner/register', 'POST', ownerData);
    },

    logout() {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        localStorage.removeItem('owner');
        window.location.href = 'login.html';
    },

    isAuthenticated() {
        return !!localStorage.getItem('token');
    },

    getUser() {
        const user = localStorage.getItem('user');
        return user ? JSON.parse(user) : null;
    },

    getOwner() {
        const owner = localStorage.getItem('owner');
        return owner ? JSON.parse(owner) : null;
    },

    isOwner() {
        return !!localStorage.getItem('owner');
    }
};

const parkingAPI = {
    async search(lat, lng, radius = 5) {
        const data = await apiCall(`/parkings/search?lat=${lat}&lng=${lng}&radius=${radius}`, 'GET');
        // Normalisation : le backend renvoie { parkings: [...] }
        return {
            parkings: data.parkings || data.data || []
        };
    },

    async getDetails(parkingId) {
        const data = await apiCall(`/parkings/${parkingId}`, 'GET');
        // Normalisation : le backend renvoie { parking: {...} }
        return {
            parking: data.parking || data.data || data
        };
    },

    async getSubscriptions(parkingId) {
        const data = await apiCall(`/parkings/${parkingId}/subscriptions`, 'GET');
        return {
            subscriptions: data.subscriptions || data.data || []
        };
    },

    async enter(parkingId) {
        // Le backend attend un body JSON avec parking_id
        return await apiCall(`/parkings/${parkingId}/enter`, 'POST', { parking_id: parkingId });
    }
};

const reservationAPI = {
    async create(reservationData) {
        return await apiCall('/reservations', 'POST', reservationData);
    },

    async getUserReservations(userId) {
        const data = await apiCall('/user/reservations', 'GET');
        return {
            reservations: data.reservations || data.data || []
        };
    },

    async getInvoice(reservationId) {
        return await apiCall(`/reservations/${reservationId}/invoice`, 'GET');
    }
};

const subscriptionAPI = {
    async subscribe(subscriptionData) {
        return await apiCall('/subscriptions', 'POST', subscriptionData);
    },

    async getUserSubscriptions(userId) {
        return await apiCall(`/users/${userId}/subscriptions`, 'GET');
    }
};

const stationnementAPI = {
    async exitParking(stationnementId) {
        // Le backend attend stationnement_id dans le body et l'URL doit matcher /parkings/{id}/exit
        // L'ID dans l'URL n'est pas utilisé côté serveur, on peut donc réutiliser stationnementId.
        return await apiCall(`/parkings/${stationnementId}/exit`, 'POST', {
            stationnement_id: stationnementId
        });
    },

    async getUserStationnements(userId) {
        const data = await apiCall('/user/stationnements', 'GET');
        return {
            stationnements: data.stationnements || data.data || []
        };
    }
};

const invoiceAPI = {
    async create(invoiceData) {
        return await apiCall('/invoices', 'POST', invoiceData);
    },

    async getUserInvoices(userId) {
        return await apiCall(`/users/${userId}/invoices`, 'GET');
    }
};
