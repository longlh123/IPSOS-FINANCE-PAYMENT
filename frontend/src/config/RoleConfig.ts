export const BaseVisibility = {
  sidebar: {
    visible_projects: true,
    visible_transactions: false
  },
  projects: {
    visible_project_table: true,
    functions: {
      visible_add_new_project: false,
      visible_change_status_of_project: false,
      visible_edit_project: false,
      visible_view_gifts: false,
      visible_view_transactions: false,
      visible_view_parttime_employees: false
    }
  },
  gifts: {
    functions: {
      visible_import_respondents: false
    }
  },
  employees: {
    visible_employee_table: true,
    functions: {
      visible_import_employees: true
    }
  },
  transactions: {
    components: {
      visible_deposited: false,
      visible_spent: false,
      visible_balance: false,
      visible_transactions: false,
      visible_merchantinfor: false,
      visible_transactionsmanager: false
    }
  }
}

export const VisibilityConfig = {
  Admin : {
    inherit: null,
    override: {
      sidebar: {
        visible_transactions: true
      },
      projects: {
        functions: {
          visible_add_new_project: true,
          visible_change_status_of_project: true,
          visible_edit_project: true,
          visible_view_gifts: true,
          visible_view_transactions: true,
          visible_view_parttime_employees: true
        }
      },
      gifts: {
        functions: {
          visible_import_respondents: true
        }
      },
      employees: {
        visible_employee_table: true,
        functions: {
          visible_import_employees: true
        }
      },
      transactions: {
        components: {
          visible_deposited: true,
          visible_spent: true,
          visible_balance: true,
          visible_transactions: true,
          visible_merchantinfor: true,
          visible_transactionsmanager: true
        }
      }
    }
  },
  Finance: {
    inherit: "Admin",
    override: {
      sidebar: {
        visible_projects: false,
        visible_transactions: true
      }
    }
  },
  Scripter: {
    inherit: null,
    override: {
      sidebar: {
        visible_projects: true,
      },
      projects: {
        visible_project_table: true,
        functions: {
          visible_add_new_project: true,
          visible_change_status_of_project: true,
          visible_edit_project: true,
          visible_view_gifts: true,
          visible_view_transactions: false,
          visible_view_parttime_employees: true
        }
      },
      employees: {
        visible_employee_table: true,
        functions: {
          visible_import_employees: true
        }
      },
    }
  }
} as const;

function deepMerge(target: any, source: any): any {
  const output = structuredClone(target); // clone object gốc

  for (const key in source) {
    if (source.hasOwnProperty(key)) {
      // nếu value là object, merge đệ quy
      if (
        typeof source[key] === "object" &&
        source[key] !== null &&
        !Array.isArray(source[key])
      ) {
        output[key] = deepMerge(output[key] || {}, source[key]);
      } else {
        // nếu là giá trị đơn giản thì ghi đè
        output[key] = source[key];
      }
    }
  }

  return output;
}

export type RoleName = keyof typeof VisibilityConfig;

export function buildVisibility(role: RoleName): any {
  const roleConfig = VisibilityConfig[role];

  if (!roleConfig) return BaseVisibility;

  if (roleConfig.inherit) {
    // merge base + inherited + override
    const inherited = buildVisibility(roleConfig.inherit as RoleName);
    return deepMerge(deepMerge(BaseVisibility, inherited), roleConfig.override);
  }

  // merge base + override nếu không có inherit
  return deepMerge(BaseVisibility, roleConfig.override);
}