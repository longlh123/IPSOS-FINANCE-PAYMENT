const host = "http://localhost:8000"

export const ApiConfig = {
  project: {
    viewProjects: `${host}/api/project-management/projects`,
    viewProject: `${host}/api/project-management/projects?platform=ifield&created_user_id=`,
    updateStatusOfProject: `${host}/api/project-management/projects/{projectId}/status`,
    getMetadata: `${host}/api/project-management/metadata`,
    addProject: `${host}/api/project-management/projects/store`,
    editProject: `${host}/`,
    getProjectTypes: `${host}/api/project-management/project-types`,
    getDepartments: `${host}/api/project-management/departments`,
    getTeams: `${host}/api/project-management/{department_id}/teams`,
    viewTransactions: `${host}/api/project-management/{projectId}/transactions/view`,
    viewEmployees: `${host}/api/project-management/{projectId}/employees/view`,
  },
  respondent: {
    viewRespondents: `${host}/api/project-management/projects/{projectId}/respondents/show`,
  },
  employee: {
    viewEmployees: `${host}/api/project-management/projects/{projectId}/employees/show`
  },
  vinnet: {
    viewMerchantInfo: `${host}/api/project-management/vinnet/merchant/view`,
    viewMerchantAccount: `${host}/api/project-management/vinnet/merchantinfo`,
    performMultipleTransactions: `${host}/api/project-management/vinnet/transactions`,
    changeMerchantKey: `${host}/api/project-management/vinnet/change-key`,
    verifiedVinnetToken: `${host}/api/project-management/project/verify-vinnet-token`,
    storeVinnetToken: `${host}/api/project-management/project/store-vinnet-token`,
    rejectTransaction: `${host}/api/project-management/vinnet/reject-transaction`
  },
  gotit: {
    performTransaction: `${host}/api/project-management/got-it/transaction`,
    rejectTransaction: `${host}/api/project-management/got-it/reject-transaction`
  },
  techcombank_panel: {
    viewTechcombankPanel: `${host}/api/techcombank-panel/users`,
    viewTechcombankSurveys: `${host}/api/techcombank-panel/surveys`,
    getTotalMembers: `${host}/api/techcombank-panel/total-members`,
    getProvince: `${host}/api/techcombank-panel/provinces`,
    getAgeGroup: `${host}/api/techcombank-panel/age-group`,
    getOccupation: `${host}/api/techcombank-panel/occupation`,
    getProducts: `${host}/api/techcombank-panel/products`,
    getVennProducts: `${host}/api/techcombank-panel/venn-products`,
    getChannels: `${host}/api/techcombank-panel/channels`,
    getCount: `${host}/api/techcombank-panel/{table_name}/{column_name}`,
    getPanellist: `${host}/api/techcombank-panel/panellist`,
  },
  account: {
    login: `${host}/api/login`, //y
    forgotPassword: `${host}/api/forgot-password`,
    resetPassword: `${host}/api/reset-password`,
  },
};
