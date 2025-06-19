export const VisibilityConfig = {
  'Admin': {
    sidebar: {
      visible_projects: true,
      visible_vinnet: true
    },
    projects: {
      visible_project_table: true,
      functions: {
        visible_add_new_project: true,
        visible_change_status_of_project: true,
        visible_edit_project: true,
        visible_view_gift_manager: true,
      },
      components: {
        visible_employees: true,
        visible_respondents: true
      }
    },
    vinnet: {
      components: {
        visible_deposited: true,
        visible_spent: true,
        visible_balance: true,
        visible_transactions: true,
        visible_merchantinfor: true,
        visible_vinnettransactionsmanager: true
      }
    }
  },
  'Finance': {
    sidebar: {
      visible_projects: true,
      visible_vinnet: true
    },
    projects: {
      visible_project_table: true,
      functions: {
        visible_add_new_project: false,
        visible_change_status_of_project: false,
        visible_edit_project: false,
        visible_view_gift_manager: true,
      },
      components: {
        visible_employees: true,
        visible_respondents: true
      }
    },
    vinnet: {
      components: {
        visible_deposited: true,
        visible_spent: true,
        visible_balance: true,
        visible_transactions: true,
        visible_merchantinfor: false,
        visible_vinnettransactionsmanager: true
      }
    }
  },
  'Field Manager': {
    sidebar: {
      visible_projects: true,
      visible_vinnet: true
    },
    projects: {
      visible_project_table: true,
      functions: {
        visible_add_new_project: false,
        visible_change_status_of_project: false,
        visible_edit_project: false,
        visible_view_gift_manager: true,
      },
      components: {
        visible_employees: true,
        visible_respondents: false
      }
    },
    vinnet: {
      components: {
        visible_deposited: true,
        visible_spent: true,
        visible_balance: true,
        visible_transactions: true,
        visible_merchantinfor: true,
        visible_vinnettransactionsmanager: true
      }
    }
  }
};
