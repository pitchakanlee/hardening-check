<?php
// =============================================================
// == file: CIS_Debian_Linux_12_Benchmark_v1.1.0.pdf
// == section: 7
// =============================================================
return [
    // --- 7 System Maintenance  ---
    [ 'id' => '7', 'title' => 'System Maintenance', 'type' => 'header' ],

    // --- 7.1 System File Permissions ---
    [ 'id' => '7.1', 'title' => 'System File Permissions', 'type' => 'header' ],


    // --- 7.1.1 Ensure permissions on /etc/passwd are configured ---
    [
        'id' => '7.1.1', 'title' => 'Ensure permissions on /etc/passwd are configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  PASSWD_FILE="/etc/passwd"
  EXPECTED_MODE_MASK="0133"
  EXPECTED_OWNER="root"
  EXPECTED_GROUP="root"

  if [ ! -f "$PASSWD_FILE" ]; then
    a_output_fail+=(" - Password file '$PASSWD_FILE' does not exist. Cannot verify permissions.")
  else
    FILE_STATS=$(stat -Lc '%#a:%U:%G' "$PASSWD_FILE" 2>/dev/null)
    CURRENT_MODE=$(echo "$FILE_STATS" | cut -d':' -f1)
    CURRENT_OWNER=$(echo "$FILE_STATS" | cut -d':' -f2)
    CURRENT_GROUP=$(echo "$FILE_STATS" | cut -d':' -f3)
    
    IS_MODE_OK="true"
    IS_OWNER_OK="true"
    IS_GROUP_OK="true"

    if [ $(( CURRENT_MODE & EXPECTED_MODE_MASK )) -gt 0 ]; then
      IS_MODE_OK="false"
      a_output_fail+=(" - Permissions for '$PASSWD_FILE' are '$CURRENT_MODE' (incorrect). Should be '0644' or more restrictive.")
    else
      a_output_pass+=(" - Permissions for '$PASSWD_FILE' are '$CURRENT_MODE' (0644 or more restrictive, correct).")
    fi

    if [[ "$CURRENT_OWNER" != "$EXPECTED_OWNER" ]]; then
      IS_OWNER_OK="false"
      a_output_fail+=(" - Owner for '$PASSWD_FILE' is '$CURRENT_OWNER' (incorrect). Should be '$EXPECTED_OWNER'.")
    else
      a_output_pass+=(" - Owner for '$PASSWD_FILE' is '$CURRENT_OWNER' (correct).")
    fi

    if [[ "$CURRENT_GROUP" != "$EXPECTED_GROUP" ]]; then
      IS_GROUP_OK="false"
      a_output_fail+=(" - Group for '$PASSWD_FILE' is '$CURRENT_GROUP' (incorrect). Should be '$EXPECTED_GROUP'.")
    else
      a_output_pass+=(" - Group for '$PASSWD_FILE' is '$CURRENT_GROUP' (correct).")
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 7.1.2 Ensure permissions on /etc/passwd- are configured ---
    [
        'id' => '7.1.2', 'title' => 'Ensure permissions on /etc/passwd- are configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  PASSWD_BACKUP_FILE="/etc/passwd-"
  EXPECTED_MODE_MASK="0133"
  EXPECTED_OWNER="root"
  EXPECTED_GROUP="root"

  if [ ! -f "$PASSWD_BACKUP_FILE" ]; then
    a_output_fail+=(" - Password backup file '$PASSWD_BACKUP_FILE' does not exist. Cannot verify permissions.")
  else
    FILE_STATS=$(stat -Lc '%#a:%U:%G' "$PASSWD_BACKUP_FILE" 2>/dev/null)
    CURRENT_MODE=$(echo "$FILE_STATS" | cut -d':' -f1)
    CURRENT_OWNER=$(echo "$FILE_STATS" | cut -d':' -f2)
    CURRENT_GROUP=$(echo "$FILE_STATS" | cut -d':' -f3)
    
    if [ $(( CURRENT_MODE & EXPECTED_MODE_MASK )) -gt 0 ]; then
      a_output_fail+=(" - Permissions for '$PASSWD_BACKUP_FILE' are '$CURRENT_MODE' (incorrect). Should be '0644' or more restrictive.")
    else
      a_output_pass+=(" - Permissions for '$PASSWD_BACKUP_FILE' are '$CURRENT_MODE' (0644 or more restrictive, correct).")
    fi

    if [[ "$CURRENT_OWNER" != "$EXPECTED_OWNER" ]]; then
      a_output_fail+=(" - Owner for '$PASSWD_BACKUP_FILE' is '$CURRENT_OWNER' (incorrect). Should be '$EXPECTED_OWNER'.")
    else
      a_output_pass+=(" - Owner for '$PASSWD_BACKUP_FILE' is '$CURRENT_OWNER' (correct).")
    fi

    if [[ "$CURRENT_GROUP" != "$EXPECTED_GROUP" ]]; then
      a_output_fail+=(" - Group for '$PASSWD_BACKUP_FILE' is '$CURRENT_GROUP' (incorrect). Should be '$EXPECTED_GROUP'.")
    else
      a_output_pass+=(" - Group for '$PASSWD_BACKUP_FILE' is '$CURRENT_GROUP' (correct).")
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 7.1.3 Ensure permissions on /etc/group are configured ---
    [
        'id' => '7.1.3', 'title' => 'Ensure permissions on /etc/group are configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  GROUP_FILE="/etc/group"
  EXPECTED_MODE_MASK="0133"
  EXPECTED_OWNER="root"
  EXPECTED_GROUP="root"

  if [ ! -f "$GROUP_FILE" ]; then
    a_output_fail+=(" - Group file '$GROUP_FILE' does not exist. Cannot verify permissions.")
  else
    FILE_STATS=$(stat -Lc '%#a:%U:%G' "$GROUP_FILE" 2>/dev/null)
    CURRENT_MODE=$(echo "$FILE_STATS" | cut -d':' -f1)
    CURRENT_OWNER=$(echo "$FILE_STATS" | cut -d':' -f2)
    CURRENT_GROUP=$(echo "$FILE_STATS" | cut -d':' -f3)
    
    if [ $(( CURRENT_MODE & EXPECTED_MODE_MASK )) -gt 0 ]; then
      a_output_fail+=(" - Permissions for '$GROUP_FILE' are '$CURRENT_MODE' (incorrect). Should be '0644' or more restrictive.")
    else
      a_output_pass+=(" - Permissions for '$GROUP_FILE' are '$CURRENT_MODE' (0644 or more restrictive, correct).")
    fi

    if [[ "$CURRENT_OWNER" != "$EXPECTED_OWNER" ]]; then
      a_output_fail+=(" - Owner for '$GROUP_FILE' is '$CURRENT_OWNER' (incorrect). Should be '$EXPECTED_OWNER'.")
    else
      a_output_pass+=(" - Owner for '$GROUP_FILE' is '$CURRENT_OWNER' (correct).")
    fi

    if [[ "$CURRENT_GROUP" != "$EXPECTED_GROUP" ]]; then
      a_output_fail+=(" - Group for '$GROUP_FILE' is '$CURRENT_GROUP' (incorrect). Should be '$EXPECTED_GROUP'.")
    else
      a_output_pass+=(" - Group for '$GROUP_FILE' is '$CURRENT_GROUP' (correct).")
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 7.1.4 Ensure permissions on /etc/group- are configured ---
    [
        'id' => '7.1.4', 'title' => 'Ensure permissions on /etc/group- are configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  GROUP_BACKUP_FILE="/etc/group-"
  EXPECTED_MODE_MASK="0133"
  EXPECTED_OWNER="root"
  EXPECTED_GROUP="root"

  if [ ! -f "$GROUP_BACKUP_FILE" ]; then
    a_output_pass+=(" - Group backup file '$GROUP_BACKUP_FILE' does not exist (correct, as it's not always present).")
  else
    FILE_STATS=$(stat -Lc '%#a:%U:%G' "$GROUP_BACKUP_FILE" 2>/dev/null)
    CURRENT_MODE=$(echo "$FILE_STATS" | cut -d':' -f1)
    CURRENT_OWNER=$(echo "$FILE_STATS" | cut -d':' -f2)
    CURRENT_GROUP=$(echo "$FILE_STATS" | cut -d':' -f3)
    
    if [ $(( CURRENT_MODE & EXPECTED_MODE_MASK )) -gt 0 ]; then
      a_output_fail+=(" - Permissions for '$GROUP_BACKUP_FILE' are '$CURRENT_MODE' (incorrect). Should be '0644' or more restrictive.")
    else
      a_output_pass+=(" - Permissions for '$GROUP_BACKUP_FILE' are '$CURRENT_MODE' (0644 or more restrictive, correct).")
    fi

    if [[ "$CURRENT_OWNER" != "$EXPECTED_OWNER" ]]; then
      a_output_fail+=(" - Owner for '$GROUP_BACKUP_FILE' is '$CURRENT_OWNER' (incorrect). Should be '$EXPECTED_OWNER'.")
    else
    a_output_pass+=(" - Owner for '$GROUP_BACKUP_FILE' is '$CURRENT_OWNER' (correct).")
    fi

    if [[ "$CURRENT_GROUP" != "$EXPECTED_GROUP" ]]; then
      a_output_fail+=(" - Group for '$GROUP_BACKUP_FILE' is '$CURRENT_GROUP' (incorrect). Should be '$EXPECTED_GROUP'.")
    else
      a_output_pass+=(" - Group for '$GROUP_BACKUP_FILE' is '$CURRENT_GROUP' (correct).")
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],
    
    // --- 7.1.5 Ensure permissions on /etc/shadow are configured ---
    [
        'id' => '7.1.5', 'title' => 'Ensure permissions on /etc/shadow are configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  SHADOW_FILE="/etc/shadow"
  EXPECTED_MODE_MASK="0137"
  EXPECTED_OWNER="root"
  EXPECTED_GROUPS=("root" "shadow")

  if [ ! -f "$SHADOW_FILE" ]; then
    a_output_fail+=(" - Shadow file '$SHADOW_FILE' does not exist. Cannot verify permissions.")
  else
    FILE_STATS=$(stat -Lc '%#a:%U:%G' "$SHADOW_FILE" 2>/dev/null)
    CURRENT_MODE=$(echo "$FILE_STATS" | cut -d':' -f1)
    CURRENT_OWNER=$(echo "$FILE_STATS" | cut -d':' -f2)
    CURRENT_GROUP=$(echo "$FILE_STATS" | cut -d':' -f3)
    
    IS_MODE_OK="true"
    IS_OWNER_OK="true"
    IS_GROUP_OK="true"

    if [ $(( CURRENT_MODE & EXPECTED_MODE_MASK )) -gt 0 ]; then
      IS_MODE_OK="false"
      a_output_fail+=(" - Permissions for '$SHADOW_FILE' are '$CURRENT_MODE' (incorrect). Should be '0640' or more restrictive.")
    else
      a_output_pass+=(" - Permissions for '$SHADOW_FILE' are '$CURRENT_MODE' (0640 or more restrictive, correct).")
    fi

    if [[ "$CURRENT_OWNER" != "$EXPECTED_OWNER" ]]; then
      IS_OWNER_OK="false"
      a_output_fail+=(" - Owner for '$SHADOW_FILE' is '$CURRENT_OWNER' (incorrect). Should be '$EXPECTED_OWNER'.")
    else
      a_output_pass+=(" - Owner for '$SHADOW_FILE' is '$CURRENT_OWNER' (correct).")
    fi

    GROUP_MATCH="false"
    for expected_group in "${EXPECTED_GROUPS[@]}"; do
      if [[ "$CURRENT_GROUP" == "$expected_group" ]]; then
        GROUP_MATCH="true"
        break
      fi
    done

    if [[ "$GROUP_MATCH" == "false" ]]; then
      IS_GROUP_OK="false"
      a_output_fail+=(" - Group for '$SHADOW_FILE' is '$CURRENT_GROUP' (incorrect). Should be 'root' or 'shadow'.")
    else
      a_output_pass+=(" - Group for '$SHADOW_FILE' is '$CURRENT_GROUP' (correct).")
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 7.1.6 Ensure permissions on /etc/shadow- are configured ---
    [
        'id' => '7.1.6', 'title' => 'Ensure permissions on /etc/shadow- are configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  SHADOW_BACKUP_FILE="/etc/shadow-"
  EXPECTED_MODE_MASK="0137"
  EXPECTED_OWNER="root"
  EXPECTED_GROUPS=("root" "shadow")

  if [ ! -f "$SHADOW_BACKUP_FILE" ]; then
    a_output_pass+=(" - Shadow backup file '$SHADOW_BACKUP_FILE' does not exist (correct, as it's not always present).")
  else
    FILE_STATS=$(stat -Lc '%#a:%U:%G' "$SHADOW_BACKUP_FILE" 2>/dev/null)
    CURRENT_MODE=$(echo "$FILE_STATS" | cut -d':' -f1)
    CURRENT_OWNER=$(echo "$FILE_STATS" | cut -d':' -f2)
    CURRENT_GROUP=$(echo "$FILE_STATS" | cut -d':' -f3)
    
    if [ $(( CURRENT_MODE & EXPECTED_MODE_MASK )) -gt 0 ]; then
      a_output_fail+=(" - Permissions for '$SHADOW_BACKUP_FILE' are '$CURRENT_MODE' (incorrect). Should be '0640' or more restrictive.")
    else
      a_output_pass+=(" - Permissions for '$SHADOW_BACKUP_FILE' are '$CURRENT_MODE' (0640 or more restrictive, correct).")
    fi

    if [[ "$CURRENT_OWNER" != "$EXPECTED_OWNER" ]]; then
      a_output_fail+=(" - Owner for '$SHADOW_BACKUP_FILE' is '$CURRENT_OWNER' (incorrect). Should be '$EXPECTED_OWNER'.")
    else
      a_output_pass+=(" - Owner for '$SHADOW_BACKUP_FILE' is '$CURRENT_OWNER' (correct).")
    fi

    GROUP_MATCH="false"
    for expected_group in "${EXPECTED_GROUPS[@]}"; do
      if [[ "$CURRENT_GROUP" == "$expected_group" ]]; then
        GROUP_MATCH="true"
        break
      fi
    done

    if [[ "$GROUP_MATCH" == "false" ]]; then
      a_output_fail+=(" - Group for '$SHADOW_BACKUP_FILE' is '$CURRENT_GROUP' (incorrect). Should be 'root' or 'shadow'.")
    else
      a_output_pass+=(" - Group for '$SHADOW_BACKUP_FILE' is '$CURRENT_GROUP' (correct).")
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 7.1.7 Ensure permissions on /etc/gshadow are configured ---
    [
        'id' => '7.1.7', 'title' => 'Ensure permissions on /etc/gshadow are configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  GSHADOW_FILE="/etc/gshadow"
  EXPECTED_MODE_MASK="0137"
  EXPECTED_OWNER="root"
  EXPECTED_GROUPS=("root" "shadow")

  if [ ! -f "$GSHADOW_FILE" ]; then
    a_output_fail+=(" - Gshadow file '$GSHADOW_FILE' does not exist. Cannot verify permissions.")
  else
    FILE_STATS=$(stat -Lc '%#a:%U:%G' "$GSHADOW_FILE" 2>/dev/null)
    CURRENT_MODE=$(echo "$FILE_STATS" | cut -d':' -f1)
    CURRENT_OWNER=$(echo "$FILE_STATS" | cut -d':' -f2)
    CURRENT_GROUP=$(echo "$FILE_STATS" | cut -d':' -f3)
    
    if [ $(( CURRENT_MODE & EXPECTED_MODE_MASK )) -gt 0 ]; then
      a_output_fail+=(" - Permissions for '$GSHADOW_FILE' are '$CURRENT_MODE' (incorrect). Should be '0640' or more restrictive.")
    else
      a_output_pass+=(" - Permissions for '$GSHADOW_FILE' are '$CURRENT_MODE' (0640 or more restrictive, correct).")
    fi

    if [[ "$CURRENT_OWNER" != "$EXPECTED_OWNER" ]]; then
      a_output_fail+=(" - Owner for '$GSHADOW_FILE' is '$CURRENT_OWNER' (incorrect). Should be '$EXPECTED_OWNER'.")
    else
      a_output_pass+=(" - Owner for '$GSHADOW_FILE' is '$CURRENT_OWNER' (correct).")
    fi

    GROUP_MATCH="false"
    for expected_group in "${EXPECTED_GROUPS[@]}"; do
      if [[ "$CURRENT_GROUP" == "$expected_group" ]]; then
        GROUP_MATCH="true"
        break
      fi
    done

    if [[ "$GROUP_MATCH" == "false" ]]; then
      a_output_fail+=(" - Group for '$GSHADOW_FILE' is '$CURRENT_GROUP' (incorrect). Should be 'root' or 'shadow'.")
    else
      a_output_pass+=(" - Group for '$GSHADOW_FILE' is '$CURRENT_GROUP' (correct).")
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 7.1.8 Ensure permissions on /etc/gshadow- are configured ---
    [
        'id' => '7.1.8', 'title' => 'Ensure permissions on /etc/gshadow- are configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  GSHADOW_BACKUP_FILE="/etc/gshadow-"
  EXPECTED_MODE_MASK="0137"
  EXPECTED_OWNER="root"
  EXPECTED_GROUPS=("root" "shadow")

  if [ ! -f "$GSHADOW_BACKUP_FILE" ]; then
    a_output_pass+=(" - Gshadow backup file '$GSHADOW_BACKUP_FILE' does not exist (correct, as it's not always present).")
  else
    FILE_STATS=$(stat -Lc '%#a:%U:%G' "$GSHADOW_BACKUP_FILE" 2>/dev/null)
    CURRENT_MODE=$(echo "$FILE_STATS" | cut -d':' -f1)
    CURRENT_OWNER=$(echo "$FILE_STATS" | cut -d':' -f2)
    CURRENT_GROUP=$(echo "$FILE_STATS" | cut -d':' -f3)
    
    if [ $(( CURRENT_MODE & EXPECTED_MODE_MASK )) -gt 0 ]; then
      a_output_fail+=(" - Permissions for '$GSHADOW_BACKUP_FILE' are '$CURRENT_MODE' (incorrect). Should be '0640' or more restrictive.")
    else
      a_output_pass+=(" - Permissions for '$GSHADOW_BACKUP_FILE' are '$CURRENT_MODE' (0640 or more restrictive, correct).")
    fi

    if [[ "$CURRENT_OWNER" != "$EXPECTED_OWNER" ]]; then
      a_output_fail+=(" - Owner for '$GSHADOW_BACKUP_FILE' is '$CURRENT_OWNER' (incorrect). Should be '$EXPECTED_OWNER'.")
    else
      a_output_pass+=(" - Owner for '$GSHADOW_BACKUP_FILE' is '$CURRENT_OWNER' (correct).")
    fi

    GROUP_MATCH="false"
    for expected_group in "${EXPECTED_GROUPS[@]}"; do
      if [[ "$CURRENT_GROUP" == "$expected_group" ]]; then
        GROUP_MATCH="true"
        break
      fi
    done

    if [[ "$GROUP_MATCH" == "false" ]]; then
      a_output_fail+=(" - Group for '$GSHADOW_BACKUP_FILE' is '$CURRENT_GROUP' (incorrect). Should be 'root' or 'shadow'.")
    else
      a_output_pass+=(" - Group for '$GSHADOW_BACKUP_FILE' is '$CURRENT_GROUP' (correct).")
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],
    
    // --- 7.1.9 Ensure permissions on /etc/shells are configured ---
    [
        'id' => '7.1.9', 'title' => 'Ensure permissions on /etc/shells are configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  SHELLS_FILE="/etc/shells"
  EXPECTED_MODE_MASK="0133"
  EXPECTED_OWNER="root"
  EXPECTED_GROUP="root"

  if [ ! -f "$SHELLS_FILE" ]; then
    a_output_fail+=(" - Shells file '$SHELLS_FILE' does not exist. Cannot verify permissions.")
  else
    FILE_STATS=$(stat -Lc '%#a:%U:%G' "$SHELLS_FILE" 2>/dev/null)
    CURRENT_MODE=$(echo "$FILE_STATS" | cut -d':' -f1)
    CURRENT_OWNER=$(echo "$FILE_STATS" | cut -d':' -f2)
    CURRENT_GROUP=$(echo "$FILE_STATS" | cut -d':' -f3)
    
    if [ $(( CURRENT_MODE & EXPECTED_MODE_MASK )) -gt 0 ]; then
      a_output_fail+=(" - Permissions for '$SHELLS_FILE' are '$CURRENT_MODE' (incorrect). Should be '0644' or more restrictive.")
    else
      a_output_pass+=(" - Permissions for '$SHELLS_FILE' are '$CURRENT_MODE' (0644 or more restrictive, correct).")
    fi

    if [[ "$CURRENT_OWNER" != "$EXPECTED_OWNER" ]]; then
      a_output_fail+=(" - Owner for '$SHELLS_FILE' is '$CURRENT_OWNER' (incorrect). Should be '$EXPECTED_OWNER'.")
    else
      a_output_pass+=(" - Owner for '$SHELLS_FILE' is '$CURRENT_OWNER' (correct).")
    fi

    if [[ "$CURRENT_GROUP" != "$EXPECTED_GROUP" ]]; then
      a_output_fail+=(" - Group for '$SHELLS_FILE' is '$CURRENT_GROUP' (incorrect). Should be '$EXPECTED_GROUP'.")
    else
      a_output_pass+=(" - Group for '$SHELLS_FILE' is '$CURRENT_GROUP' (correct).")
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 7.1.10 Ensure permissions on /etc/security/opasswd are configured ---
    [
        'id' => '7.1.10', 'title' => 'Ensure permissions on /etc/security/opasswd are configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  OPASSWD_FILE="/etc/security/opasswd"
  OPASSWD_OLD_FILE="/etc/security/opasswd.old"
  EXPECTED_MODE_MASK="0177"
  EXPECTED_OWNER="root"
  EXPECTED_GROUP="root"

  audit_opasswd_file() {
    local file_path="$1"
    local file_display_name="$2"

    if [ ! -f "$file_path" ]; then
      a_output_pass+=(" - $file_display_name does not exist (correct if not in use).")
    else
      FILE_STATS=$(stat -Lc '%#a:%U:%G' "$file_path" 2>/dev/null)
      CURRENT_MODE=$(echo "$FILE_STATS" | cut -d':' -f1)
      CURRENT_OWNER=$(echo "$FILE_STATS" | cut -d':' -f2)
      CURRENT_GROUP=$(echo "$FILE_STATS" | cut -d':' -f3)
      
      LOCAL_FILE_STATUS="PASS"

      if [ $(( CURRENT_MODE & EXPECTED_MODE_MASK )) -gt 0 ]; then
        a_output_fail+=(" - Permissions for $file_display_name are '$CURRENT_MODE' (incorrect). Should be '0600' or more restrictive.")
        LOCAL_FILE_STATUS="FAIL"
      fi

      if [[ "$CURRENT_OWNER" != "$EXPECTED_OWNER" ]]; then
        a_output_fail+=(" - Owner for $file_display_name is '$CURRENT_OWNER' (incorrect). Should be '$EXPECTED_OWNER'.")
        LOCAL_FILE_STATUS="FAIL"
      fi

      if [[ "$CURRENT_GROUP" != "$EXPECTED_GROUP" ]]; then
        a_output_fail+=(" - Group for $file_display_name is '$CURRENT_GROUP' (incorrect). Should be '$EXPECTED_GROUP'.")
        LOCAL_FILE_STATUS="FAIL"
      fi

      if [[ "$LOCAL_FILE_STATUS" == "PASS" ]]; then
        a_output_pass+=(" - $file_display_name has permissions '$CURRENT_MODE', owner '$CURRENT_OWNER', and group '$CURRENT_GROUP' (correct).")
      fi
    fi
  }

  audit_opasswd_file "$OPASSWD_FILE" "/etc/security/opasswd"

  audit_opasswd_file "$OPASSWD_OLD_FILE" "/etc/security/opasswd.old"

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 7.1.11 Ensure world writable files and directories are secured ---
    [
        'id' => '7.1.11', 'title' => 'Ensure world writable files and directories are secured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash 
{ 
   l_output="" l_output2="" 
   l_smask='01000' 
   a_file=(); a_dir=()
   a_path=(! -path "/run/user/*" -a ! -path "/proc/*" -a ! -path "*/containerd/*" -a ! -path "*/kubelet/pods/*" -a ! -path "*/kubelet/plugins/*" -a ! -path "/sys/*" -a ! -path "/snap/*") 
   while IFS= read -r l_mount; do 
      while IFS= read -r -d $'\0' l_file; do 
         if [ -e "$l_file" ]; then 
            [ -f "$l_file" ] && a_file+=("$l_file") 
            if [ -d "$l_file" ]; then
               l_mode="$(stat -Lc '%#a' "$l_file")" 
               [ ! $(( $l_mode & $l_smask )) -gt 0 ] && a_dir+=("$l_file") 
            fi 
         fi 
      done < <(find "$l_mount" -xdev \( "${a_path[@]}" \) \( -type f -o -type d \) -perm -0002 -print0 2> /dev/null) 
   done < <(findmnt -Dkerno fstype,target | awk '($1 !~ /^\s*(nfs|proc|smb|vfat|iso9660|efivarfs|selinuxfs)/ && $2 !~ /^(\/run\/user\/|\/tmp|\/var\/tmp)/){print $2}') 
   if ! (( ${#a_file[@]} > 0 )); then 
      l_output="$l_output\n  - No world writable files exist on the local filesystem." 
   else 
      l_output2="$l_output2\n - There are \"$(printf '%s' "${#a_file[@]}")\" World writable files on the system.\n   - The following is a list of World writable files:\n$(printf '%s\n' "${a_file[@]}")\n   - end of list\n" 
   fi 
   if ! (( ${#a_dir[@]} > 0 )); then 
      l_output="$l_output\n  - Sticky bit is set on world writable directories on the local filesystem." 
   else 
      l_output2="$l_output2\n - There are \"$(printf '%s' "${#a_dir[@]}")\" World writable directories without the sticky bit on the system.\n   - The following is a list of World writable directories without the sticky bit:\n$(printf '%s\n' "${a_dir[@]}")\n   - end of list\n" 
   fi 
   unset a_path; unset a_arr; unset a_file; unset a_dir
   if [ -z "$l_output2" ]; then 
      echo -e "\n- Audit Result:\n  ** PASS **\n - * Correctly configured * :\n$l_output\n" 
   else 
      echo -e "\n- Audit Result:\n  ** FAIL **\n - * Reasons for audit failure * :\n$l_output2" 
      [ -n "$l_output" ] && echo -e "- * Correctly configured * :\n$l_output\n" 
   fi 
}
BASH
    ],

    // --- 7.1.12 Ensure no files or directories without an owner and a group exist ---
    [
        'id' => '7.1.12', 'title' => 'Ensure no files or directories without an owner and a group exist', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash 
{ 
   l_output="" l_output2="" 
   a_nouser=(); a_nogroup=() # Initialize arrays 
   a_path=(! -path "/run/user/*" -a ! -path "/proc/*" -a ! -path "*/containerd/*" -a ! -path "*/kubelet/pods/*" -a ! -path "*/kubelet/plugins/*" -a ! -path "/sys/fs/cgroup/memory/*" -a ! -path "/var/*/private/*") 
   while IFS= read -r l_mount; do 
      while IFS= read -r -d $'\0' l_file; do 
         if [ -e "$l_file" ]; then 
            while IFS=: read -r l_user l_group; do 
               [ "$l_user" = "UNKNOWN" ] && a_nouser+=("$l_file") 
               [ "$l_group" = "UNKNOWN" ] && a_nogroup+=("$l_file") 
            done < <(stat -Lc '%U:%G' "$l_file") 
         fi 
      done < <(find "$l_mount" -xdev \( "${a_path[@]}" \) \( -type f -o -type d \) \( -nouser -o -nogroup \) -print0 2> /dev/null) 
   done < <(findmnt -Dkerno fstype,target | awk '($1 !~ /^\s*(nfs|proc|smb|vfat|iso9660|efivarfs|selinuxfs)/ && $2 !~ /^\/run\/user\//){print $2}') 
   if ! (( ${#a_nouser[@]} > 0 )); then 
      l_output="$l_output\n  - No files or directories without a owner exist on the local filesystem." 
   else 
      l_output2="$l_output2\n  - There are \"$(printf '%s' "${#a_nouser[@]}")\" unowned files or directories on the system.\n   - The following is a list of unowned files and/or directories:\n$(printf '%s\n' "${a_nouser[@]}")\n   - end of list" 
   fi 
   if ! (( ${#a_nogroup[@]} > 0 )); then 
      l_output="$l_output\n  - No files or directories without a group exist on the local filesystem." 
   else 
      l_output2="$l_output2\n  - There are \"$(printf '%s' "${#a_nogroup[@]}")\" ungrouped files or directories on the system.\n   - The following is a list of ungrouped files and/or directories:\n$(printf '%s\n' "${a_nogroup[@]}")\n   - end of list" 
   fi  
   unset a_path; unset a_arr ; unset a_nouser; unset a_nogroup
   if [ -z "$l_output2" ]; then
      echo -e "\n- Audit Result:\n  ** PASS **\n - * Correctly configured * :\n$l_output\n" 
   else 
      echo -e "\n- Audit Result:\n  ** FAIL **\n - * Reasons for audit failure * :\n$l_output2" 
      [ -n "$l_output" ] && echo -e "\n- * Correctly configured * :\n$l_output\n" 
   fi 
}
BASH
    ],
    
    // --- 7.1.13 Ensure SUID and SGID files are reviewed ---
    [
        'id' => '7.1.13', 'title' => 'Ensure SUID and SGID files are reviewed', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 7.2 Local User and Group Settings ---
    [ 'id' => '7.2', 'title' => 'Local User and Group Settings', 'type' => 'header' ],

    // --- 7.2.1 Ensure accounts in /etc/passwd use shadowed passwords ---
    [
        'id' => '7.2.1', 'title' => 'Ensure accounts in /etc/passwd use shadowed passwords', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  PASSWD_FILE="/etc/passwd"

  UNSHADOWED_USERS=$(awk -F: '($2 != "x" ) { print "User: \"" $1 "\" is not set to shadowed passwords" }' "$PASSWD_FILE" 2>/dev/null)

  if [[ -z "$UNSHADOWED_USERS" ]]; then
    a_output_pass+=(" - All accounts in '$PASSWD_FILE' use shadowed passwords (correct).")
  else
    a_output_fail+=(" - The following accounts in '$PASSWD_FILE' do NOT use shadowed passwords:")
    while IFS= read -r line; do
      a_output_fail+=("   $line")
    done <<< "$UNSHADOWED_USERS"
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 7.2.2 Ensure /etc/shadow password fields are not empty ---
    [
        'id' => '7.2.2', 'title' => 'Ensure /etc/shadow password fields are not empty', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  SHADOW_FILE="/etc/shadow"

  EMPTY_PASSWORD_USERS=$(awk -F: '($2 == "" ) { print $1 " does not have a password" }' "$SHADOW_FILE" 2>/dev/null)

  if [[ -z "$EMPTY_PASSWORD_USERS" ]]; then
    a_output_pass+=(" - All accounts in '$SHADOW_FILE' have non-empty password fields (correct).")
  else
    a_output_fail+=(" - The following accounts in '$SHADOW_FILE' have empty password fields:")
    while IFS= read -r line; do
      a_output_fail+=("   $line")
    done <<< "$EMPTY_PASSWORD_USERS"
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 7.2.3 Ensure all groups in /etc/passwd exist in /etc/group ---
    [
        'id' => '7.2.3', 'title' => 'Ensure all groups in /etc/passwd exist in /etc/group', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  PASSWD_FILE="/etc/passwd"
  GROUP_FILE="/etc/group"

  if [ ! -f "$PASSWD_FILE" ]; then
    a_output_fail+=(" - Required file '$PASSWD_FILE' does not exist. Cannot perform audit.")
  fi
  if [ ! -f "$GROUP_FILE" ]; then
    a_output_fail+=(" - Required file '$GROUP_FILE' does not exist. Cannot perform audit.")
  fi

  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    a_passwd_group_gid=($(awk -F: '{print $4}' "$PASSWD_FILE" | sort -u))
    a_group_gid=($(awk -F: '{print $3}' "$GROUP_FILE" | sort -u))

    MISSING_GIDS=()
    for passwd_gid in "${a_passwd_group_gid[@]}"; do
      if ! printf "%s\n" "${a_group_gid[@]}" | grep -q -w "$passwd_gid"; then
        MISSING_GIDS+=("$passwd_gid")
      fi
    done

    if [ "${#MISSING_GIDS[@]}" -eq 0 ]; then
      a_output_pass+=(" - All GIDs in '$PASSWD_FILE' exist in '$GROUP_FILE' (correct).")
    else
      a_output_fail+=(" - The following GIDs from '$PASSWD_FILE' do NOT exist in '$GROUP_FILE':")
      for l_gid in "${MISSING_GIDS[@]}"; do
        USERS_WITH_MISSING_GID=$(awk -F: '($4 == '"$l_gid"') {print "  - User: \"" $1 "\" has GID: \"" $4 "\" which does not exist in /etc/group"}' "$PASSWD_FILE")
        while IFS= read -r line; do
          a_output_fail+=("$line")
        done <<< "$USERS_WITH_MISSING_GID"
      done
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 7.2.4 Ensure shadow group is empty ---
    [
        'id' => '7.2.4', 'title' => 'Ensure shadow group is empty', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  GROUP_FILE="/etc/group"
  PASSWD_FILE="/etc/passwd"

  if [ ! -f "$GROUP_FILE" ]; then
    a_output_fail+=(" - Required file '$GROUP_FILE' does not exist. Cannot perform audit.")
  fi
  if [ ! -f "$PASSWD_FILE" ]; then
    a_output_fail+=(" - Required file '$PASSWD_FILE' does not exist. Cannot perform audit.")
  fi

  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    SHADOW_GROUP_MEMBERS=$(awk -F: '($1=="shadow") {print $NF}' "$GROUP_FILE" 2>/dev/null)

    if [[ -z "$SHADOW_GROUP_MEMBERS" ]]; then
      a_output_pass+=(" - 'shadow' group in '$GROUP_FILE' has no members (correct).")
    else
      a_output_fail+=(" - 'shadow' group in '$GROUP_FILE' contains members: '$SHADOW_GROUP_MEMBERS'.")
    fi

    SHADOW_GID=$(getent group shadow | awk -F: '{print $3}' | xargs 2>/dev/null)
    if [[ -z "$SHADOW_GID" ]]; then
      a_output_fail+=(" - Could not determine GID for 'shadow' group. Cannot verify primary group ownership.")
    else
      USERS_WITH_SHADOW_PRIMARY_GROUP=$(awk -F: -v shadow_gid="$SHADOW_GID" '($4 == shadow_gid) {print "  - user: \"" $1 "\" primary group is the shadow group"}' "$PASSWD_FILE" 2>/dev/null)

      if [[ -z "$USERS_WITH_SHADOW_PRIMARY_GROUP" ]]; then
        a_output_pass+=(" - No users have 'shadow' as their primary group (correct).")
      else
        a_output_fail+=(" - The following users have 'shadow' as their primary group:")
        while IFS= read -r line; do
          a_output_fail+=("$line")
        done <<< "$USERS_WITH_SHADOW_PRIMARY_GROUP"
      fi
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],
    
    // --- 7.2.5 Ensure no duplicate UIDs exist  ---
    [
        'id' => '7.2.5', 'title' => 'Ensure no duplicate UIDs exist ', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  PASSWD_FILE="/etc/passwd"

  if [ ! -f "$PASSWD_FILE" ]; then
    a_output_fail+=(" - Required file '$PASSWD_FILE' does not exist. Cannot perform audit.")
  else
    DUPLICATE_UID_OUTPUT=$(
      cut -f3 -d":" "$PASSWD_FILE" | sort -n | uniq -c |
      while read -r l_count l_uid; do
        if [ "$l_count" -gt 1 ]; then
          USERS_WITH_DUPLICATE_UID=$(awk -F: '($3 == n) { print $1 }' n="$l_uid" "$PASSWD_FILE" | xargs)
          echo "Duplicate UID: \"$l_uid\" Users: \"$USERS_WITH_DUPLICATE_UID\""
        fi
      done
    )

    if [[ -z "$DUPLICATE_UID_OUTPUT" ]]; then
      a_output_pass+=(" - No duplicate UIDs found in '$PASSWD_FILE' (correct).")
    else
      a_output_fail+=(" - Duplicate UIDs found in '$PASSWD_FILE':")
      while IFS= read -r line; do
        a_output_fail+=("   $line")
      done <<< "$DUPLICATE_UID_OUTPUT"
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 7.2.6 Ensure no duplicate GIDs exist ---
    [
        'id' => '7.2.6', 'title' => 'Ensure no duplicate GIDs exist', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  GROUP_FILE="/etc/group"

  if [ ! -f "$GROUP_FILE" ]; then
    a_output_fail+=(" - Required file '$GROUP_FILE' does not exist. Cannot perform audit.")
  else
    DUPLICATE_GID_OUTPUT=$(
      cut -f3 -d":" "$GROUP_FILE" | sort -n | uniq -c |
      while read -r l_count l_gid; do
        if [ "$l_count" -gt 1 ]; then
          GROUPS_WITH_DUPLICATE_GID=$(awk -F: '($3 == n) { print $1 }' n="$l_gid" "$GROUP_FILE" | xargs)
          echo "Duplicate GID: \"$l_gid\" Groups: \"$GROUPS_WITH_DUPLICATE_GID\""
        fi
      done
    )

    if [[ -z "$DUPLICATE_GID_OUTPUT" ]]; then
      a_output_pass+=(" - No duplicate GIDs found in '$GROUP_FILE' (correct).")
    else
      a_output_fail+=(" - Duplicate GIDs found in '$GROUP_FILE':")
      while IFS= read -r line; do
        a_output_fail+=("   $line")
      done <<< "$DUPLICATE_GID_OUTPUT"
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 7.2.7 Ensure no duplicate user names exist ---
    [
        'id' => '7.2.7', 'title' => 'Ensure no duplicate user names exist', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  PASSWD_FILE="/etc/passwd"

  if [ ! -f "$PASSWD_FILE" ]; then
    a_output_fail+=(" - Required file '$PASSWD_FILE' does not exist. Cannot perform audit.")
  else
    DUPLICATE_USERNAME_OUTPUT=$(
      cut -f1 -d":" "$PASSWD_FILE" | sort | uniq -c |
      while read -r l_count l_user; do
        if [ "$l_count" -gt 1 ]; then
          USERS_WITH_DUPLICATE_USERNAME=$(awk -F: '($1 == n) { print $1 }' n="$l_user" "$PASSWD_FILE" | xargs)
          echo "Duplicate User: \"$l_user\" Users: \"$USERS_WITH_DUPLICATE_USERNAME\""
        fi
      done
    )

    if [[ -z "$DUPLICATE_USERNAME_OUTPUT" ]]; then
      a_output_pass+=(" - No duplicate user names found in '$PASSWD_FILE' (correct).")
    else
      a_output_fail+=(" - Duplicate user names found in '$PASSWD_FILE':")
      while IFS= read -r line; do
        a_output_fail+=("   $line")
      done <<< "$DUPLICATE_USERNAME_OUTPUT"
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 7.2.8 Ensure no duplicate group names exist ---
    [
        'id' => '7.2.8', 'title' => 'Ensure no duplicate group names exist', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  GROUP_FILE="/etc/group"

  if [ ! -f "$GROUP_FILE" ]; then
    a_output_fail+=(" - Required file '$GROUP_FILE' does not exist. Cannot perform audit.")
  else
    DUPLICATE_GROUP_NAME_OUTPUT=$(
      cut -f1 -d":" "$GROUP_FILE" | sort | uniq -c |
      while read -r l_count l_group_name; do
        if [ "$l_count" -gt 1 ]; then
          GROUPS_WITH_DUPLICATE_NAME=$(awk -F: '($1 == n) { print $1 }' n="$l_group_name" "$GROUP_FILE" | xargs)
          echo "Duplicate Group: \"$l_group_name\" Groups: \"$GROUPS_WITH_DUPLICATE_NAME\""
        fi
      done
    )

    if [[ -z "$DUPLICATE_GROUP_NAME_OUTPUT" ]]; then
      a_output_pass+=(" - No duplicate group names found in '$GROUP_FILE' (correct).")
    else
      a_output_fail+=(" - Duplicate group names found in '$GROUP_FILE':")
      while IFS= read -r line; do
        a_output_fail+=("   $line")
      done <<< "$DUPLICATE_GROUP_NAME_OUTPUT"
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],
    
    // --- 7.2.9 Ensure local interactive user home directories are configured ---
    [
        'id' => '7.2.9', 'title' => 'Ensure local interactive user home directories are configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash 
 
{ 
   l_output="" l_output2="" l_heout2="" l_hoout2="" l_haout2="" 
   l_valid_shells="^($( awk -F\/ '$NF != "nologin" {print}' /etc/shells | sed -rn '/^\//{s,/,\\\\/,g;p}' | paste -s -d '|' - ))$" 
   unset a_uarr && a_uarr=()
   while read -r l_epu l_eph; do
      a_uarr+=("$l_epu $l_eph") 
   done <<< "$(awk -v pat="$l_valid_shells" -F: '$(NF) ~ pat { print $1 " " $(NF-1) }' /etc/passwd)" 
   l_asize="${#a_uarr[@]}" 
   [ "$l_asize " -gt "10000" ] && echo -e "\n  ** INFO **\n  - \"$l_asize\" Local interactive users found on the system\n  - This may be a long running check\n" 
   while read -r l_user l_home; do 
      if [ -d "$l_home" ]; then 
         l_mask='0027' 
         l_max="$( printf '%o' $(( 0777 & ~$l_mask)) )" 
         while read -r l_own l_mode; do 
            [ "$l_user" != "$l_own" ] && l_hoout2="$l_hoout2\n  - User: \"$l_user\" Home \"$l_home\" is owned by: \"$l_own\"" 
            if [ $(( $l_mode & $l_mask )) -gt 0 ]; then 
               l_haout2="$l_haout2\n  - User: \"$l_user\" Home \"$l_home\" is mode: \"$l_mode\" should be mode: \"$l_max\" or more restrictive" 
            fi 
         done <<< "$(stat -Lc '%U %#a' "$l_home")" 
      else 
         l_heout2="$l_heout2\n  - User: \"$l_user\" Home \"$l_home\" Doesn't exist" 
      fi 
   done <<< "$(printf '%s\n' "${a_uarr[@]}")" 
   [ -z "$l_heout2" ] && l_output="$l_output\n   - home directories exist" || l_output2="$l_output2$l_heout2" 
   [ -z "$l_hoout2" ] && l_output="$l_output\n   - own their home directory" || l_output2="$l_output2$l_hoout2" 
   [ -z "$l_haout2" ] && l_output="$l_output\n   - home directories are mode: \"$l_max\" or more restrictive" || l_output2="$l_output2$l_haout2" 
   [ -n "$l_output" ] && l_output="  - All local interactive users:$l_output" 
   if [ -z "$l_output2" ]; then 
      echo -e "\n- Audit Result:\n  ** PASS **\n - * Correctly configured * :\n$l_output" 
   else 
      echo -e "\n- Audit Result:\n  ** FAIL **\n - * Reasons for audit failure * :\n$l_output2" 
      [ -n "$l_output" ] && echo -e "\n- * Correctly configured * :\n$l_output" 
   fi 
} 
BASH
    ],

    // --- 7.2.10 Ensure local interactive user dot files access is configured ---
    [
        'id' => '7.2.10', 'title' => 'Ensure local interactive user dot files access is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
   a_output2=()
   a_output3=()
   l_maxsize="1000" # Maximum number of local interactive users before warning

   # Build regex of valid shells excluding nologin
   l_valid_shells="^($(awk -F/ '$NF != "nologin" {print}' /etc/shells | sed -rn '/^\//{s,/,\\/,g;p}' | paste -s -d '|' -))$"

   # Create array with local users and their home directories
   a_user_and_home=()
   while read -r l_local_user l_local_user_home; do
      [[ -n "$l_local_user" && -n "$l_local_user_home" ]] && a_user_and_home+=("$l_local_user:$l_local_user_home")
   done <<< "$(awk -v pat="$l_valid_shells" -F: '$(NF) ~ pat { print $1 " " $(NF-1) }' /etc/passwd)"

   l_asize="${#a_user_and_home[@]}"
   if [ "$l_asize" -gt "$l_maxsize" ]; then
      printf '%s\n' "" "  ** INFO **" \
         "  - \"$l_asize\" Local interactive users found on the system" \
         "  - This may be a long running check" ""
   fi

   # Function to check file access
   file_access_chk() {
      a_access_out=()
      l_max="$(printf '%o' $((0777 & ~$l_mask)))"
      if [ $((l_mode & l_mask)) -gt 0 ]; then
         a_access_out+=("  - File: \"$l_hdfile\" is mode: \"$l_mode\" and should be mode: \"$l_max\" or more restrictive")
      fi
      if [[ ! "$l_owner" =~ ($l_user) ]]; then
         a_access_out+=("  - File: \"$l_hdfile\" owned by: \"$l_owner\" and should be owned by \"${l_user//|/ or }\"")
      fi
      if [[ ! "$l_gowner" =~ ($l_group) ]]; then
         a_access_out+=("  - File: \"$l_hdfile\" group owned by: \"$l_gowner\" and should be group owned by \"${l_group//|/ or }\"")
      fi
   }

   while IFS=: read -r l_user l_home; do
      a_dot_file=()
      a_netrc=()
      a_netrc_warn=()
      a_bhout=()
      a_hdirout=()

      if [ -d "$l_home" ]; then
         l_group="$(id -gn "$l_user" | xargs)"
         l_group="${l_group// /|}"

         while IFS= read -r -d $'\0' l_hdfile; do
            while read -r l_mode l_owner l_gowner; do
               case "$(basename "$l_hdfile")" in
                  .forward | .rhost )
                     a_dot_file+=("  - File: \"$l_hdfile\" exists")
                     ;;
                  .netrc )
                     l_mask='0177'
                     file_access_chk
                     if [ "${#a_access_out[@]}" -gt 0 ]; then
                        a_netrc+=("${a_access_out[@]}")
                     else
                        a_netrc_warn+=("   - File: \"$l_hdfile\" exists")
                     fi
                     ;;
                  .bash_history )
                     l_mask='0177'
                     file_access_chk
                     [ "${#a_access_out[@]}" -gt 0 ] && a_bhout+=("${a_access_out[@]}")
                     ;;
                  * )
                     l_mask='0133'
                     file_access_chk
                     [ "${#a_access_out[@]}" -gt 0 ] && a_hdirout+=("${a_access_out[@]}")
                     ;;
               esac
            done < <(stat -Lc '%#a %U %G' "$l_hdfile")
         done < <(find "$l_home" -xdev -type f -name '.*' -print0)
      fi

      if [[ "${#a_dot_file[@]}" -gt 0 || "${#a_netrc[@]}" -gt 0 || "${#a_bhout[@]}" -gt 0 || "${#a_hdirout[@]}" -gt 0 ]]; then
         a_output2+=(" - User: \"$l_user\" Home Directory: \"$l_home\"" "${a_dot_file[@]}" "${a_netrc[@]}" "${a_bhout[@]}" "${a_hdirout[@]}")
      fi

      if [ "${#a_netrc_warn[@]}" -gt 0 ]; then
         a_output3+=(" - User: \"$l_user\" Home Directory: \"$l_home\"" "${a_netrc_warn[@]}")
      fi
   done <<< "$(printf '%s\n' "${a_user_and_home[@]}")"

   if [ "${#a_output2[@]}" -le 0 ]; then
      [ "${#a_output3[@]}" -gt 0 ] && printf '%s\n' "  ** WARNING **" "${a_output3[@]}"
      printf '%s\n' "- Audit Result:" "  ** PASS **"
   else
      printf '%s\n' "- Audit Result:" "  ** FAIL **" " - * Reasons for audit failure * :" "${a_output2[@]}" ""
      [ "${#a_output3[@]}" -gt 0 ] && printf '%s\n' "  ** WARNING **" "${a_output3[@]}"
   fi
}

BASH
    ],
];
