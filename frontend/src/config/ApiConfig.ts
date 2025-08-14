const host = "https://www.ippay.vn"
//const host = "http://127.0.0.1:8000"

export const ApiConfig = {
  project: {
    viewProjects: `${host}/api/project-management/projects`,
    viewProject: `${host}/api/project-management/projects?platform=ifield&created_user_id=`,
    updateStatusOfProject: `${host}/api/project-management/projects/{project_id}/status`,
    addProject: `${host}/api/project-management/projects`,
    editProject: `${host}/`,
    getProjectTypes: `${host}/api/project-management/project-types`,
    getDepartments: `${host}/api/project-management/departments`,
    getTeams: `${host}/api/project-management/{department_id}/teams`,
  },
  respondent: {
    viewRespondents: `${host}/api/project-management/projects/{project_id}/respondents/show`,
  },
  employee: {
    viewEmployees: `${host}/api/project-management/projects/{project_id}/employees/show`
  },
  vinnet: {
    viewMerchantInfo: `${host}/api/project-management/vinnet/merchant/view`,
    viewMerchantAccount: `${host}/api/project-management/vinnet/merchantinfo`,
    performMultipleTransactions: `${host}/api/project-management/vinnet/transactions`,
    changeMerchantKey: `${host}/api/project-management/vinnet/change-key`,
    verifiedVinnetToken: `${host}/api/project-management/project/verify-vinnet-token`,
    storeVinnetToken: `${host}/api/project-management/project/store-vinnet-token`,
    viewVinnetTransactions: `${host}/api/project-management/vinnet/transactions/view`,
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
