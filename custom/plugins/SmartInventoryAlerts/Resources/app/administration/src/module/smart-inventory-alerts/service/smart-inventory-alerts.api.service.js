const { Application } = Shopware;
const ApiService = Shopware.Classes.ApiService;

class SmartInventoryAlertsApiService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'smart-inventory-alerts') {
        super(httpClient, loginService, apiEndpoint);
    }

    fetchDashboard(limit = 10) {
        const headers = this.getBasicHeaders();
        return this.httpClient
            .get(`_action/${this.getApiBasePath()}/dashboard`, {
                params: { limit },
                headers
            })
            .then((response) => ApiService.handleResponse(response));
    }
}

Application.addServiceProvider('SmartInventoryAlertsApiService', (container) => {
    const initContainer = Application.getContainer('init');

    return new SmartInventoryAlertsApiService(
        container.httpClient,
        initContainer.loginService
    );
});
