<?php
// =============================================================
// == file: CIS_Debian_Linux_12_Benchmark_v1.1.0.pdf
// == section: 6
// =============================================================
return [
    // --- 6 Logging and Auditing ---
    [ 'id' => '6', 'title' => 'Logging and Auditing', 'type' => 'header' ],

    // --- 6.1 System Logging ---
    [ 'id' => '6.1', 'title' => 'System Logging', 'type' => 'header' ],


    // --- 6.1.1 Configure systemd-journald service ---
    [ 'id' => '6.1.1', 'title' => 'Configure systemd-journald service', 'type' => 'header' ],

    // --- 6.1.1.1 Ensure journald service is enabled and active ---
    [
        'id' => '6.1.1.1', 'title' => 'Ensure journald service is enabled and active', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"
  JOURNALD_ENABLED_STATUS=$(systemctl is-enabled systemd-journald.service 2>/dev/null)

  if [[ "$JOURNALD_ENABLED_STATUS" == "static" ]]; then
    a_output_pass+=(" - 'systemd-journald.service' is enabled with status 'static' (correct).")
  else
    a_output_fail+=(" - 'systemd-journald.service' is not enabled with status 'static'. Current status: '$JOURNALD_ENABLED_STATUS'.")
  fi

  JOURNALD_ACTIVE_STATUS=$(systemctl is-active systemd-journald.service 2>/dev/null)

  if [[ "$JOURNALD_ACTIVE_STATUS" == "active" ]]; then
    a_output_pass+=(" - 'systemd-journald.service' is active (correct).")
  else
    a_output_fail+=(" - 'systemd-journald.service' is not active. Current status: '$JOURNALD_ACTIVE_STATUS'.")
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

    // --- 6.1.1.2 Ensure journald log file access is configured ---
    [
        'id' => '6.1.1.2', 'title' => 'Ensure journald log file access is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
   a_output=() a_output2=()
   l_systemd_config_file="/etc/tmpfiles.d/systemd.conf"
   l_analyze_cmd="$(readlink -f /bin/systemd-analyze)"
   f_file_chk()
   {
      l_maxperm="$( printf '%o' $(( 0777 & ~$l_perm_mask )) )"
      if [ $(( $l_mode & $l_perm_mask )) -le 0 ] || [[ "$l_type" = "Directory" && "$l_mode" =~ 275(0|5) ]]; then
         a_out+=("  - $l_type \"$l_logfile\" access is:" \          "    mode: \"$l_mode\", owned by: \"$l_user\", and group owned by: \"$l_group\"")
      else
         a_out2+=("  - $l_type \"$l_logfile\" access is:" \          "    mode: \"$l_mode\", owned by: \"$l_user\", and group owned by: \"$l_group\"" \          "    should be mode: \"$l_maxperm\" or more restrictive")
      fi
   }
   while IFS= read -r l_file; do
      l_file="$(tr -d '# ' <<< "$l_file")" a_out=() a_out2=()
      l_logfile_perms_line="$(awk '($1~/^(f|d)$/ && $2~/\/\S+/ && $3~/[0-9]{3,}/){print $2 ":" $3 ":" $4 ":" $5}' "$l_file")"
      while IFS=: read -r l_logfile l_mode l_user l_group; do
         if [ -d "$l_logfile" ]; then
            l_perm_mask="0027" l_type="Directory"
            grep -Psq '^(\/run|\/var\/lib\/systemd)\b' <<< "$l_logfile" && l_perm_mask="0022"
         else
            l_perm_mask="0137" l_type="File"
         fi
         grep -Psq '^(\/run|\/var\/lib\/systemd)\b' <<< "$l_logfile" && l_perm_mask="0022"
         f_file_chk
      done <<< "$l_logfile_perms_line"
      [ "${#a_out[@]}" -gt "0" ] && a_output+=(" - File: \"$l_file\" sets:" "${a_out[@]}")
      [ "${#a_out2[@]}" -gt "0" ] && a_output2+=(" - File: \"$l_file\" sets:" "${a_out2[@]}")
   done < <($l_analyze_cmd cat-config "$l_systemd_config_file" | tac | grep -Pio '^\h*#\h*\/[^#\n\r\h]+\.conf\b')
   if [ "${#a_output2[@]}" -le 0 ]; then
      printf '%s\n' "" "- Audit Result:" "  ** PASS **" "${a_output[@]}" ""
   else
      printf '%s\n' "" "- Audit Result:" "  ** REVIEW **" \
      " -  Review file access to ensure they are set IAW site policy:" "${a_output2[@]}"
      [ "${#a_output[@]}" -gt 0 ] && printf '%s\n' "" "- Correctly set:" "${a_output[@]}" ""
   fi
}
BASH
    ],

    // --- 6.1.1.3 Ensure journald log file rotation is configured ---
    [
        'id' => '6.1.1.3', 'title' => 'Ensure journald log file rotation is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
   a_output=() a_output2=() l_analyze_cmd="$(readlink -f /bin/systemd-analyze)"
   l_systemd_config_file="systemd/journald.conf"
   a_parameters=("SystemMaxUse=^.+$" "SystemKeepFree=^.+$" "RuntimeMaxUse=^.+$" "RuntimeKeepFree=^.+$" "MaxFileSec=^.+$")
   f_config_file_parameter_chk()
   {
      l_used_parameter_setting=""
      while IFS= read -r l_file; do
         l_file="$(tr -d '# ' <<< "$l_file")"
         l_used_parameter_setting="$(grep -PHs -- '^\h*'"$l_parameter_name"'\b' "$l_file" | tail -n 1)"
         [ -n "$l_used_parameter_setting" ] && break
      done < <($l_analyze_cmd cat-config "$l_systemd_config_file" | tac | grep -Pio '^\h*#\h*\/[^#\n\r\h]+\.conf\b')
      if [ -n "$l_used_parameter_setting" ]; then
         while IFS=: read -r l_file_name l_file_parameter; do
            while IFS="=" read -r l_file_parameter_name l_file_parameter_value; do
               if grep -Pq -- "$l_parameter_value" <<< "$l_file_parameter_value"; then
                  a_output+=("  - Parameter: \"${l_file_parameter_name// /}\"" \                   "    set to: \"${l_file_parameter_value// /}\"" \                   "    in the file: \"$l_file_name\"") 
               fi
            done <<< "$l_file_parameter"
         done <<< "$l_used_parameter_setting"
      else
         a_output2+=("  - Parameter: \"$l_parameter_name\" is not set in an included file" \          "   *** Note: ***" "   \"$l_parameter_name\" May be set in a file that's ignored by load procedure") 
      fi
   }
   for l_input_parameter in "${a_parameters[@]}"; do
      while IFS="=" read -r l_parameter_name l_parameter_value; do
         l_parameter_name="${l_parameter_name// /}";
         l_parameter_value="${l_parameter_value// /}"
         l_value_out="${l_parameter_value//-/ through }";
         l_value_out="${l_value_out//|/ or }"
         l_value_out="$(tr -d '(){}' <<< "$l_value_out")"
         f_config_file_parameter_chk
      done <<< "$l_input_parameter"
   done
   if [ "${#a_output2[@]}" -le 0 ]; then
      printf '%s\n' "" "- Audit Result:" "  ** PASS **" "${a_output[@]}" ""
   else
      printf '%s\n' "" "- Audit Result:" "  ** FAIL **" " - Reason(s) for audit failure:" "${a_output2[@]}"
      [ "${#a_output[@]}" -gt 0 ] && printf '%s\n' "" "- Correctly set:" "${a_output[@]}" ""
   fi
}
BASH
    ],

    // --- 6.1.1.4 Ensure only one logging system is in use ---
    [
        'id' => '6.1.1.4', 'title' => 'Ensure only one logging system is in use', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
   l_output="" l_output2=""
   if systemctl is-active --quiet rsyslog; then
      l_output="$l_output\n - rsyslog is in use\n- follow the recommendations in Configure rsyslog subsection only"
   elif systemctl is-active --quiet systemd-journald; then
      l_output="$l_output\n - journald is in use\n- follow the recommendations in Configure journald subsection only"
   else
   echo -e “unable to determine system logging”
      l_output2="$l_output2\n - unable to determine system logging\n- Configure only ONE system logging: rsyslog OR journald"
   fi
   if [ -z "$l_output2" ]; then
      echo -e "\n- Audit Result:\n  ** PASS **\n$l_output\n"
   else
      echo -e "\n- Audit Result:\n  ** FAIL **\n - Reason(s) for audit failure:\n$l_output2"
   fi
}
BASH
    ],

    // --- 6.1.2 Configure journald ---
    [ 'id' => '6.1.2', 'title' => 'Ensure permissions on SSH private host key files are configured', 'type' => 'header'],

    // --- 6.1.2.1 Configure systemd-journal-remote ---
    [
        'id' => '6.1.2.1', 'title' => 'Configure systemd-journal-remote', 'type' => 'header'],

    // --- 6.1.2.1.1 Ensure systemd-journal-remote is installed ---
    [
        'id' => '6.1.2.1.1', 'title' => 'Ensure systemd-journal-remote is installed', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  if systemctl is-active --quiet rsyslog.service; then
    a_output_pass+=(" - Rsyslog service is active; skipping check for 'systemd-journal-remote' installation as per policy.")
    echo "- Audit Result:"
    echo "  ** SKIPPED ** (Skipped due to rsyslog in use)"
    printf '%s\n' "${a_output_pass[@]}"
    exit 0
  fi

  if dpkg-query -s systemd-journal-remote &>/dev/null; then
    a_output_pass+=(" - 'systemd-journal-remote' package is installed (correct).")
  else
    a_output_fail+=(" - 'systemd-journal-remote' package is not installed.")
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

    // --- 6.1.2.1.2 Ensure systemd-journal-upload authentication is configured ---
    [
        'id' => '6.1.2.1.2', 'title' => 'Ensure systemd-journal-upload authentication is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  if systemctl is-active --quiet rsyslog.service; then
    a_output_pass+=(" - Rsyslog service is active; skipping check for 'systemd-journal-upload' authentication as per policy.")
    echo "- Audit Result:"
    echo "  ** SKIPPED ** (Skipped due to rsyslog in use)"
    printf '%s\n' "${a_output_pass[@]}"
    exit 0
  fi

  l_analyze_cmd="$(readlink -f /bin/systemd-analyze)"
  l_systemd_config_file="systemd/journal-upload.conf"

  a_parameters=("URL=^.+$" "ServerKeyFile=^.+$" "ServerCertificateFile=^.+$" "TrustedCertificateFile=^.+$")

  f_config_file_parameter_chk() {
    local param_name="$1"
    local param_value_regex="$2"
    local found_param_line=""

    found_param_line=$($l_analyze_cmd cat-config "$l_systemd_config_file" 2>/dev/null | \
                       grep -Pio '^\h*#\h*\/[^#\n\r\h]+\.conf\b|^\h*'${param_name}'\b' | \
                       tac | grep -P -m 1 '^\h*'${param_name}'\b')

    if [[ -n "$found_param_line" ]]; then
      local current_value=$(echo "$found_param_line" | awk -F'=' '{print $2}' | xargs)
      a_output_pass+=(" - Parameter '${param_name}' is set to: '${current_value}'.")
    else
      a_output_fail+=(" - Parameter '${param_name}' is NOT explicitly set in '$l_systemd_config_file' or included files.")
    fi
  }

  for l_input_parameter in "${a_parameters[@]}"; do
    IFS="=" read -r l_parameter_name l_parameter_value <<< "$l_input_parameter"
    f_config_file_parameter_chk "$l_parameter_name" "$l_parameter_value"
  done

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    printf '%s\n' "${a_output_pass[@]}"
    echo "  (Manual review of certificate locations and URL conformance with local site policy is still required.)"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set (but incomplete/missing other settings):"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 6.1.2.1.3 Ensure systemd-journal-upload is enabled and active ---
    [
        'id' => '6.1.2.1.3', 'title' => 'Ensure systemd-journal-upload is enabled and active', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"
  if systemctl is-active --quiet rsyslog.service; then
    a_output_pass+=(" - Rsyslog service is active; skipping check for 'systemd-journal-upload' as per policy.")
    echo "- Audit Result:"
    echo "  ** SKIPPED ** (Skipped due to rsyslog in use)"
    printf '%s\n' "${a_output_pass[@]}"
    exit 0
  fi

  JOURNAL_UPLOAD_ENABLED_STATUS=$(systemctl is-enabled systemd-journal-upload.service 2>/dev/null)

  if [[ "$JOURNAL_UPLOAD_ENABLED_STATUS" == "enabled" ]]; then
    a_output_pass+=(" - 'systemd-journal-upload.service' is enabled (correct).")
  else
    a_output_fail+=(" - 'systemd-journal-upload.service' is not enabled. Current status: '$JOURNAL_UPLOAD_ENABLED_STATUS'.")
  fi

  JOURNAL_UPLOAD_ACTIVE_STATUS=$(systemctl is-active systemd-journal-upload.service 2>/dev/null)

  if [[ "$JOURNAL_UPLOAD_ACTIVE_STATUS" == "active" ]]; then
    a_output_pass+=(" - 'systemd-journal-upload.service' is active (correct).")
  else
    a_output_fail+=(" - 'systemd-journal-upload.service' is not active. Current status: '$JOURNAL_UPLOAD_ACTIVE_STATUS'.")
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

    // --- 6.1.2.1.4 Ensure systemd-journal-remote service is not in use ---
    [
        'id' => '6.1.2.1.4', 'title' => 'Ensure systemd-journal-remote service is not in use', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  SERVICE_NAME="systemd-journal-remote.service"
  SOCKET_NAME="systemd-journal-remote.socket"

  if systemctl is-active --quiet rsyslog.service; then
    a_output_pass+=(" - Rsyslog service is active; skipping check for 'systemd-journal-remote' service usage as per policy.")
    echo "- Audit Result:"
    echo "  ** SKIPPED ** (Skipped due to rsyslog in use)"
    printf '%s\n' "${a_output_pass[@]}"
    exit 0
  fi

  ENABLED_STATUS_CHECK=$(systemctl is-enabled "$SOCKET_NAME" "$SERVICE_NAME" 2>/dev/null | grep -P -- '^enabled')

  if [[ -z "$ENABLED_STATUS_CHECK" ]]; then
    a_output_pass+=(" - '$SOCKET_NAME' and '$SERVICE_NAME' are not enabled (correct).")
  else
    a_output_fail+=(" - '$SOCKET_NAME' or '$SERVICE_NAME' is enabled: '$ENABLED_STATUS_CHECK'.")
  fi

  ACTIVE_STATUS_CHECK=$(systemctl is-active "$SOCKET_NAME" "$SERVICE_NAME" 2>/dev/null | grep -P -- '^active')

  if [[ -z "$ACTIVE_STATUS_CHECK" ]]; then
    a_output_pass+=(" - '$SOCKET_NAME' and '$SERVICE_NAME' are not active (correct).")
  else
    a_output_fail+=(" - '$SOCKET_NAME' or '$SERVICE_NAME' is active: '$ACTIVE_STATUS_CHECK'.")
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

    // --- 6.1.2.2 Ensure journald ForwardToSyslog is disabled ---
    [
        'id' => '6.1.2.2', 'title' => 'Ensure journald ForwardToSyslog is disabled', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  if systemctl is-active --quiet rsyslog.service; then
    a_output_pass+=(" - Rsyslog service is active; skipping check for 'journald ForwardToSyslog' as per policy.")
    echo "- Audit Result:"
    echo "  ** SKIPPED ** (Skipped due to rsyslog in use)"
    printf '%s\n' "${a_output_pass[@]}"
    exit 0 
  fi

  l_analyze_cmd="$(readlink -f /bin/systemd-analyze)"
  l_systemd_config_file="systemd/journald.conf"
  a_parameters=("ForwardToSyslog=no")

  f_config_file_parameter_chk() {
    local param_name="$1"
    local param_value="$2"
    local found_param_line=""

    found_param_line=$($l_analyze_cmd cat-config "$l_systemd_config_file" 2>/dev/null | \
                       grep -Pio '^\h*#\h*\/[^#\n\r\h]+\.conf\b|^\h*'${param_name}'\b' | \
                       tac | grep -P -m 1 '^\h*'${param_name}'\b')

    if [[ -n "$found_param_line" ]]; then
      local current_value=$(echo "$found_param_line" | awk -F'=' '{print $2}' | xargs)
      if [[ "$current_value" == "$param_value" ]]; then
        a_output_pass+=(" - Parameter '${param_name}' is correctly set to: '${current_value}'.")
      else
        a_output_fail+=(" - Parameter '${param_name}' is incorrectly set to: '${current_value}'. Should be '${param_value}'.")
      fi
    else
      a_output_fail+=(" - Parameter '${param_name}' is NOT explicitly set in '$l_systemd_config_file' or included files.")
    fi
  }

  for l_input_parameter in "${a_parameters[@]}"; do
    IFS="=" read -r l_parameter_name l_parameter_value <<< "$l_input_parameter"
    f_config_file_parameter_chk "$l_parameter_name" "$l_parameter_value"
  done

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

    // --- 6.1.2.3 Ensure journald Compress is configured ---
    [
        'id' => '6.1.2.3', 'title' => 'Ensure journald Compress is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 

  if systemctl is-active --quiet rsyslog.service; then
    a_output_pass+=(" - Rsyslog service is active; skipping check for 'journald Compress' as per policy.")
    echo "- Audit Result:"
    echo "  ** PASS ** (Skipped due to rsyslog in use)"
    printf '%s\n' "${a_output_pass[@]}"
    exit 0 
  fi

  l_analyze_cmd="$(readlink -f /bin/systemd-analyze)"
  l_systemd_config_file="systemd/journald.conf"
  a_parameters=("Compress=yes")

  f_config_file_parameter_chk() {
    local param_name="$1"
    local param_value="$2"
    local found_param_line=""

    found_param_line=$($l_analyze_cmd cat-config "$l_systemd_config_file" 2>/dev/null | \
                       grep -Pio '^\h*#\h*\/[^#\n\r\h]+\.conf\b|^\h*'${param_name}'\b' | \
                       tac | grep -P -m 1 '^\h*'${param_name}'\b')

    if [[ -n "$found_param_line" ]]; then
      local current_value=$(echo "$found_param_line" | awk -F'=' '{print $2}' | xargs)
      if [[ "$current_value" == "$param_value" ]]; then
        a_output_pass+=(" - Parameter '${param_name}' is correctly set to: '${current_value}'.")
      else
        a_output_fail+=(" - Parameter '${param_name}' is incorrectly set to: '${current_value}'. Should be '${param_value}'.")
      fi
    else
      a_output_fail+=(" - Parameter '${param_name}' is NOT explicitly set in '$l_systemd_config_file' or included files.")
    fi
  }

  for l_input_parameter in "${a_parameters[@]}"; do
    IFS="=" read -r l_parameter_name l_parameter_value <<< "$l_input_parameter"
    f_config_file_parameter_chk "$l_parameter_name" "$l_parameter_value"
  done

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

    // --- 6.1.2.4 Ensure journald Storage is configured ---
    [
        'id' => '6.1.2.4', 'title' => 'Ensure journald Storage is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 

  if systemctl is-active --quiet rsyslog.service; then
    a_output_pass+=(" - Rsyslog service is active; skipping check for 'journald Storage' as per policy.")
    echo "- Audit Result:"
    echo "  ** PASS ** (Skipped due to rsyslog in use)"
    printf '%s\n' "${a_output_pass[@]}"
    exit 0
  fi

  l_analyze_cmd="$(readlink -f /bin/systemd-analyze)"
  l_systemd_config_file="systemd/journald.conf"
  a_parameters=("Storage=persistent")

  f_config_file_parameter_chk() {
    local param_name="$1"
    local param_value="$2"
    local found_param_line=""

    found_param_line=$($l_analyze_cmd cat-config "$l_systemd_config_file" 2>/dev/null | \
                       grep -Pio '^\h*#\h*\/[^#\n\r\h]+\.conf\b|^\h*'${param_name}'\b' | \
                       tac | grep -P -m 1 '^\h*'${param_name}'\b')

    if [[ -n "$found_param_line" ]]; then
      local current_value=$(echo "$found_param_line" | awk -F'=' '{print $2}' | xargs)
      if [[ "$current_value" == "$param_value" ]]; then
        a_output_pass+=(" - Parameter '${param_name}' is correctly set to: '${current_value}'.")
      else
        a_output_fail+=(" - Parameter '${param_name}' is incorrectly set to: '${current_value}'. Should be '${param_value}'.")
      fi
    else
      a_output_fail+=(" - Parameter '${param_name}' is NOT explicitly set in '$l_systemd_config_file' or included files.")
    fi
  }

  for l_input_parameter in "${a_parameters[@]}"; do
    IFS="=" read -r l_parameter_name l_parameter_value <<< "$l_input_parameter"
    f_config_file_parameter_chk "$l_parameter_name" "$l_parameter_value"
  done

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

    // --- 6.1.3 Configure rsyslog ---
    [ 'id' => '6.1.3', 'title' => 'Configure rsyslog', 'type' => 'header'],

    // --- 6.1.3.1 Ensure rsyslog is installed ---
    [
        'id' => '6.1.3.1', 'title' => 'Ensure rsyslog is installed', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  if systemctl is-active --quiet systemd-journald.service; then
    a_output_pass+=(" - Journald service is active; skipping check for 'rsyslog' installation as per policy.")
    echo "- Audit Result:"
    echo "  ** SKIPPED ** (Skipped due to journald in use)"
    printf '%s\n' "${a_output_pass[@]}"
    exit 0
  fi

  if dpkg-query -s rsyslog &>/dev/null; then
    a_output_pass+=(" - 'rsyslog' package is installed (correct).")
  else
    a_output_fail+=(" - 'rsyslog' package is not installed.")
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

    // --- 6.1.3.2 Ensure rsyslog service is enabled and active ---
    [
        'id' => '6.1.3.2', 'title' => 'Ensure rsyslog service is enabled and active', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 

  if systemctl is-active --quiet systemd-journald.service; then
    a_output_pass+=(" - Journald service is active; skipping check for 'rsyslog' service status as per policy.")
    echo "- Audit Result:"
    echo "  ** SKIPPED ** (Skipped due to journald in use)"
    printf '%s\n' "${a_output_pass[@]}"
    exit 0 
  fi

  RSYSLOG_ENABLED_STATUS=$(systemctl is-enabled rsyslog.service 2>/dev/null)

  if [[ "$RSYSLOG_ENABLED_STATUS" == "enabled" ]]; then
    a_output_pass+=(" - 'rsyslog.service' is enabled (correct).")
  else
    a_output_fail+=(" - 'rsyslog.service' is not enabled. Current status: '$RSYSLOG_ENABLED_STATUS'.")
  fi

  RSYSLOG_ACTIVE_STATUS=$(systemctl is-active rsyslog.service 2>/dev/null)

  if [[ "$RSYSLOG_ACTIVE_STATUS" == "active" ]]; then
    a_output_pass+=(" - 'rsyslog.service' is active (correct).")
  else
    a_output_fail+=(" - 'rsyslog.service' is not active. Current status: '$RSYSLOG_ACTIVE_STATUS'.")
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

    // --- 6.1.3.3 Ensure journald is configured to send logs to rsyslog ---
    [
        'id' => '6.1.3.3', 'title' => 'Ensure journald is configured to send logs to rsyslog', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 

  if systemctl is-active --quiet systemd-journald.service; then
    a_output_pass+=(" - Journald service is active; skipping check for 'journald ForwardToSyslog' configuration as per policy.")
    echo "- Audit Result:"
    echo "  ** SKIPPED ** (Skipped due to journald in use)"
    printf '%s\n' "${a_output_pass[@]}"
    exit 0 
  fi

  l_analyze_cmd="$(readlink -f /bin/systemd-analyze)"
  l_systemd_config_file="systemd/journald.conf"
  a_parameters=("ForwardToSyslog=yes")

  f_config_file_parameter_chk() {
    local param_name="$1"
    local param_value="$2"
    local found_param_line=""

    found_param_line=$($l_analyze_cmd cat-config "$l_systemd_config_file" 2>/dev/null | \
                       grep -Pio '^\h*#\h*\/[^#\n\r\h]+\.conf\b|^\h*'${param_name}'\b' | \
                       tac | grep -P -m 1 '^\h*'${param_name}'\b')

    if [[ -n "$found_param_line" ]]; then
      local current_value=$(echo "$found_param_line" | awk -F'=' '{print $2}' | xargs)
      if [[ "$current_value" == "$param_value" ]]; then
        a_output_pass+=(" - Parameter '${param_name}' is correctly set to: '${current_value}'.")
      else
        a_output_fail+=(" - Parameter '${param_name}' is incorrectly set to: '${current_value}'. Should be '${param_value}'.")
      fi
    else
      a_output_fail+=(" - Parameter '${param_name}' is NOT explicitly set in '$l_systemd_config_file' or included files.")
    fi
  }

  for l_input_parameter in "${a_parameters[@]}"; do
    IFS="=" read -r l_parameter_name l_parameter_value <<< "$l_input_parameter"
    f_config_file_parameter_chk "$l_parameter_name" "$l_parameter_value"
  done

  JOURNALD_RSYSLOG_STATUS=$(systemctl list-units --type service 2>/dev/null | grep -P -- '(journald|rsyslog)')

  if [[ -n "$JOURNALD_RSYSLOG_STATUS" ]]; then
    if echo "$JOURNALD_RSYSLOG_STATUS" | grep -q 'rsyslog.service.*loaded active running' && \
       echo "$JOURNALD_RSYSLOG_STATUS" | grep -q 'systemd-journald.service.*loaded active running'; then
      a_output_pass+=(" - Both 'systemd-journald.service' and 'rsyslog.service' are loaded and active (correct for forwarding scenario).")
    else
      a_output_fail+=(" - Either 'systemd-journald.service' or 'rsyslog.service' (or both) are not loaded/active as expected for forwarding: \n$JOURNALD_RSYSLOG_STATUS.")
    fi
  else
    a_output_fail+=(" - Neither 'systemd-journald.service' nor 'rsyslog.service' appear to be running.")
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

    // --- 6.1.3.4 Ensure rsyslog log file creation mode is configured ---
    [
        'id' => '6.1.3.4', 'title' => 'Ensure rsyslog log file creation mode is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 
  
  if systemctl is-active --quiet systemd-journald.service; then
    a_output_pass+=(" - Journald service is active; skipping check for 'rsyslog log file creation mode' as per policy.")
    echo "- Audit Result:"
    echo "  ** SKIPPED ** (Skipped due to journald in use)"
    printf '%s\n' "${a_output_pass[@]}"
    exit 0 
  fi

  l_analyze_cmd="$(readlink -f /bin/systemd-analyze)"
  l_config_file="/etc/rsyslog.conf"
  l_parameter_name='\$FileCreateMode'

  f_parameter_chk() {
    local l_used_parameter_setting="$1"
    local l_perm_mask="0137"
    local l_maxperm=$(printf '%o' $(( 0777 & ~$l_perm_mask )))

    local l_mode=$(echo "$l_used_parameter_setting" | awk '{print $2}' | xargs)
    local l_file=$(echo "$l_used_parameter_setting" | awk '{print $1}' | cut -d: -f1) 

    if [[ -z "$l_mode" ]]; then
      a_output_fail+=(" - Parameter '$l_parameter_name' is not found in any rsyslog configuration file.")
    elif [ $(( l_mode & l_perm_mask )) -gt 0 ]; then
      a_output_fail+=(" - Parameter '$l_parameter_name' in '$l_file' is incorrectly set to mode: '$l_mode'. Should be mode: '$l_maxperm' or more restrictive.")
    else
      a_output_pass+=(" - Parameter '$l_parameter_name' in '$l_file' is correctly set to mode: '$l_mode' (0640 or more restrictive).")
    fi
  }

  all_rsyslog_configs=("$l_config_file")
  
  INCLUDES_FROM_ANALYZE=$($l_analyze_cmd cat-config "${a_config_files[0]}" 2>/dev/null | tac | grep -Pio '^\h*#\h*\/[^#\n\r\h]+\.conf\b' | sed 's/^\s*#\s*//')
  
  for inc_path in $INCLUDES_FROM_ANALYZE; do
      if [ -d "$inc_path" ]; then
          while read -r -d $'\0' f; do
              all_rsyslog_configs+=("$(readlink -f "$f")")
          done < <(find -L "$inc_path" -type f -name '*.conf' -print0 2>/dev/null)
      elif [ -f "$inc_path" ]; then
          all_rsyslog_configs+=("$(readlink -f "$inc_path")")
      fi
  done

  if [ -d "/etc/rsyslog.d" ]; then
      while read -r -d $'\0' f; do
          if [[ ! " ${all_rsyslog_configs[@]} " =~ " $(readlink -f "$f") " ]]; then
              all_rsyslog_configs+=("$(readlink -f "$f")")
          fi
      done < <(find -L "/etc/rsyslog.d" -maxdepth 1 -type f -name '*.conf' -print0 2>/dev/null)
  fi

  all_rsyslog_configs=($(printf "%s\n" "${all_rsyslog_configs[@]}" | sort -u | xargs -r realpath 2>/dev/null))

  REVERSED_CONFIG_FILES=($($l_analyze_cmd cat-config "${all_rsyslog_configs[@]}" 2>/dev/null | grep -Pio '^\h*#\h*\/[^#\n\r\h]+\.conf\b' | sed 's/^\s*#\s*//' | tac))
  if [ -f "$l_config_file" ]; then
      REVERSED_CONFIG_FILES+=("$(readlink -f "$l_config_file")") 
  fi
  REVERSED_CONFIG_FILES=($(printf "%s\n" "${REVERSED_CONFIG_FILES[@]}" | awk '!seen[$0]++')) 
  EFFECTIVE_PARAM_SETTING=""
  EFFECTIVE_PARAM_FILE=""

  for file_path in "${REVERSED_CONFIG_FILES[@]}"; do
      if [ -f "$file_path" ]; then
          CURRENT_FILE_SETTING=$(grep -PHs -- '^\h*'${l_parameter_name}'\b' "$file_path" | tail -n 1)
          if [[ -n "$CURRENT_FILE_SETTING" ]]; then
              EFFECTIVE_PARAM_SETTING="$CURRENT_FILE_SETTING"
              EFFECTIVE_PARAM_FILE="$file_path"
              break 
          fi
      fi
  done

  f_parameter_chk "$EFFECTIVE_PARAM_SETTING" 

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

    // --- 6.1.3.5 Ensure rsyslog logging is configured ---
    [
        'id' => '6.1.3.5', 'title' => 'Ensure rsyslog logging is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 

  if systemctl is-active --quiet systemd-journald.service; then
    a_output_pass+=(" - Journald service is active; skipping check for 'rsyslog logging configuration' as per policy.")
    echo "- Audit Result:"
    echo "  ** SKIPPED ** (Skipped due to journald in use)"
    printf '%s\n' "${a_output_pass[@]}"
    exit 0 
  fi

  l_analyze_cmd="$(readlink -f /bin/systemd-analyze)"
  l_config_file="/etc/rsyslog.conf"

  all_rsyslog_configs=("$l_config_file")
  
  INCLUDES_FROM_ANALYZE=$($l_analyze_cmd cat-config "${a_config_files[0]}" 2>/dev/null | tac | grep -Pio '^\h*#\h*\/[^#\n\r\h]+\.conf\b' | sed 's/^\s*#\s*//')
  
  for inc_path in $INCLUDES_FROM_ANALYZE; do
      if [ -d "$inc_path" ]; then
          while read -r -d $'\0' f; do
              all_rsyslog_configs+=("$(readlink -f "$f")")
          done < <(find -L "$inc_path" -type f -name '*.conf' -print0 2>/dev/null)
      elif [ -f "$inc_path" ]; then
          all_rsyslog_configs+=("$(readlink -f "$inc_path")")
      fi
  done

  if [ -d "/etc/rsyslog.d" ]; then
      while read -r -d $'\0' f; do
          if [[ ! " ${all_rsyslog_configs[@]} " =~ " $(readlink -f "$f") " ]]; then
              all_rsyslog_configs+=("$(readlink -f "$f")")
          fi
      done < <(find -L "/etc/rsyslog.d" -maxdepth 1 -type f -name '*.conf' -print0 2>/dev/null)
  fi

  all_rsyslog_configs=($(printf "%s\n" "${all_rsyslog_configs[@]}" | sort -u | xargs -r realpath 2>/dev/null))


  REVERSED_CONFIG_FILES=($($l_analyze_cmd cat-config "${all_rsyslog_configs[@]}" 2>/dev/null | grep -Pio '^\h*#\h*\/[^#\n\r\h]+\.conf\b' | sed 's/^\s*#\s*//' | tac))
  if [ -f "$l_config_file" ]; then
      REVERSED_CONFIG_FILES+=("$(readlink -f "$l_config_file")") 
  fi
  REVERSED_CONFIG_FILES=($(printf "%s\n" "${REVERSED_CONFIG_FILES[@]}" | awk '!seen[$0]++')) 

  LOGGING_RULES_FOUND=""
  for file_path in "${REVERSED_CONFIG_FILES[@]}"; do
      if [ -f "$file_path" ]; then
          CURRENT_FILE_LOGGING=$(grep -PHs -- '^\h*[^#\n\r\/:]+\s+.*\/var\/log\/.*$' "$file_path")
          if [[ -n "$CURRENT_FILE_LOGGING" ]]; then
              LOGGING_RULES_FOUND+="$CURRENT_FILE_LOGGING\n"
          fi
      fi
  done

  if [[ -n "$LOGGING_RULES_FOUND" ]]; then
    a_output_pass+=(" - Rsyslog logging rules to /var/log/ are configured (correct).")
    a_output_pass+=("   (Manual review of content against local site policy is still required.)")
  else
    a_output_fail+=(" - No rsyslog logging rules sending to /var/log/ were found. Logging may not be configured.")
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

    // --- 6.1.3.6 Ensure rsyslog is configured to send logs to a remote log host ---
    [
        'id' => '6.1.3.6', 'title' => 'Ensure rsyslog is configured to send logs to a remote log host', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 

  if systemctl is-active --quiet systemd-journald.service; then
    a_output_pass+=(" - Rsyslog service is active; skipping check for 'rsyslog remote log host configuration' as per policy.")
    echo "- Audit Result:"
    echo "  ** SKIPPED ** (Skipped due to journald in use)"
    printf '%s\n' "${a_output_pass[@]}"
    exit 0 
  fi

  l_analyze_cmd="$(readlink -f /bin/systemd-analyze)"
  l_config_file="/etc/rsyslog.conf"

  all_rsyslog_configs=("$l_config_file")
  
  INCLUDES_FROM_ANALYZE=$($l_analyze_cmd cat-config "${a_config_files[0]}" 2>/dev/null | tac | grep -Pio '^\h*#\h*\/[^#\n\r\h]+\.conf\b' | sed 's/^\s*#\s*//')
  
  for inc_path in $INCLUDES_FROM_ANALYZE; do
      if [ -d "$inc_path" ]; then
          while read -r -d $'\0' f; do
              all_rsyslog_configs+=("$(readlink -f "$f")")
          done < <(find -L "$inc_path" -type f -name '*.conf' -print0 2>/dev/null)
      elif [ -f "$inc_path" ]; then
          all_rsyslog_configs+=("$(readlink -f "$inc_path")")
      fi
  done

  if [ -d "/etc/rsyslog.d" ]; then
      while read -r -d $'\0' f; do
          if [[ ! " ${all_rsyslog_configs[@]} " =~ " $(readlink -f "$f") " ]]; then
              all_rsyslog_configs+=("$(readlink -f "$f")")
          fi
      done < <(find -L "/etc/rsyslog.d" -maxdepth 1 -type f -name '*.conf' -print0 2>/dev/null)
  fi

  all_rsyslog_configs=($(printf "%s\n" "${all_rsyslog_configs[@]}" | sort -u | xargs -r realpath 2>/dev/null))

  REVERSED_CONFIG_FILES=($($l_analyze_cmd cat-config "${all_rsyslog_configs[@]}" 2>/dev/null | grep -Pio '^\h*#\h*\/[^#\n\r\h]+\.conf\b' | sed 's/^\s*#\s*//' | tac))
  if [ -f "$l_config_file" ]; then
      REVERSED_CONFIG_FILES+=("$(readlink -f "$l_config_file")") 
  fi
  REVERSED_CONFIG_FILES=($(printf "%s\n" "${REVERSED_CONFIG_FILES[@]}" | awk '!seen[$0]++')) 

  LOG_FORWARDING_CONFIG_FOUND=""
  for file_path in "${REVERSED_CONFIG_FILES[@]}"; do
    if [ -f "$file_path" ]; then
      BASIC_FORWARD_LINES=$(grep -Hs -- "^\h*[^#\n\r\/:]+.*@@" "$file_path") 
      if [[ -z "$BASIC_FORWARD_LINES" ]]; then 
        BASIC_FORWARD_LINES=$(grep -Hs -- "^\h*[^#\n\r\/:]+.*@" "$file_path") 
      fi
      if [[ -n "$BASIC_FORWARD_LINES" ]]; then
        LOG_FORWARDING_CONFIG_FOUND+="$file_path:$BASIC_FORWARD_LINES\n"
      fi

      ADVANCED_FORWARD_LINES=$(grep -PHsi -- '^\s*([^#]+\s+)?action\(([^#]+\s+)?\btarget=\"?[^#"]+\"?\b' "$file_path")
      if [[ -n "$ADVANCED_FORWARD_LINES" ]]; then
        LOG_FORWARDING_CONFIG_FOUND+="$file_path:$ADVANCED_FORWARD_LINES\n"
      fi
    fi
  done

  if [[ -n "$LOG_FORWARDING_CONFIG_FOUND" ]]; then
    a_output_pass+=(" - Rsyslog is configured to send logs to a remote host (correct).")
    a_output_pass+=("   (Manual review of destination host and policy is required).")
  else
    a_output_fail+=(" - Rsyslog is NOT configured to send logs to a remote host.")
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

    // --- 6.1.3.7 Ensure rsyslog is not configured to receive logs from a remote client ---
    [
        'id' => '6.1.3.7', 'title' => 'Ensure rsyslog is not configured to receive logs from a remote client', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 
  
  if systemctl is-active --quiet systemd-journald.service; then
    a_output_pass+=(" - Rsyslog service is active; skipping check for 'rsyslog remote log reception' as per policy.")
    echo "- Audit Result:"
    echo "  ** SKIPPED ** (Skipped due to journald in use)"
    printf '%s\n' "${a_output_pass[@]}"
    exit 0 
  fi

  l_analyze_cmd="$(readlink -f /bin/systemd-analyze)"
  l_config_file="/etc/rsyslog.conf"

  all_rsyslog_configs=("$l_config_file")
  
  INCLUDES_FROM_ANALYZE=$($l_analyze_cmd cat-config "${a_config_files[0]}" 2>/dev/null | tac | grep -Pio '^\h*#\h*\/[^#\n\r\h]+\.conf\b' | sed 's/^\s*#\s*//')
  
  for inc_path in $INCLUDES_FROM_ANALYZE; do
      if [ -d "$inc_path" ]; then
          while read -r -d $'\0' f; do
              all_rsyslog_configs+=("$(readlink -f "$f")")
          done < <(find -L "$inc_path" -type f -name '*.conf' -print0 2>/dev/null)
      elif [ -f "$inc_path" ]; then
          all_rsyslog_configs+=("$(readlink -f "$inc_path")")
      fi
  done

  if [ -d "/etc/rsyslog.d" ]; then
      while read -r -d $'\0' f; do
          if [[ ! " ${all_rsyslog_configs[@]} " =~ " $(readlink -f "$f") " ]]; then
              all_rsyslog_configs+=("$(readlink -f "$f")")
          fi
      done < <(find -L "/etc/rsyslog.d" -maxdepth 1 -type f -name '*.conf' -print0 2>/dev/null)
  fi

  all_rsyslog_configs=($(printf "%s\n" "${all_rsyslog_configs[@]}" | sort -u | xargs -r realpath 2>/dev/null))

  REVERSED_CONFIG_FILES=($($l_analyze_cmd cat-config "${all_rsyslog_configs[@]}" 2>/dev/null | grep -Pio '^\h*#\h*\/[^#\n\r\h]+\.conf\b' | sed 's/^\s*#\s*//' | tac))
  if [ -f "$l_config_file" ]; then
      REVERSED_CONFIG_FILES+=("$(readlink -f "$l_config_file")")
  fi
  REVERSED_CONFIG_FILES=($(printf "%s\n" "${REVERSED_CONFIG_FILES[@]}" | awk '!seen[$0]++'))

  INCOMING_LOG_CONFIG_FOUND=""
  for l_logfile in "${REVERSED_CONFIG_FILES[@]}"; do
    if [ -f "$l_logfile" ]; then
      FAIL_CHECK=$(grep -Psi -- '^\h*module\(load=\"?imtcp\"?\)' "$l_logfile")
      if [[ -n "$FAIL_CHECK" ]]; then INCOMING_LOG_CONFIG_FOUND+="- Advanced/Obsolete format entry to accept incoming logs (module(load=\"imtcp\")): '$FAIL_CHECK' found in: '$l_logfile'\n"; fi

      FAIL_CHECK=$(grep -Psi -- '^\h*input\(type=\"?imtcp\"?\b' "$l_logfile")
      if [[ -n "$FAIL_CHECK" ]]; then INCOMING_LOG_CONFIG_FOUND+="- Advanced/Obsolete format entry to accept incoming logs (input(type=\"imtcp\")): '$FAIL_CHECK' found in: '$l_logfile'\n"; fi

    fi
  done

  if [[ -z "$INCOMING_LOG_CONFIG_FOUND" ]]; then
    a_output_pass+=(" - No entries to accept incoming logs found in rsyslog configuration (correct).")
  else
    a_output_fail+=(" - Rsyslog is configured to accept incoming logs:")
    while IFS= read -r line; do
      a_output_fail+=("   $line")
    done <<< "$INCOMING_LOG_CONFIG_FOUND"
    a_output_fail+=("   (Rsyslog should not be configured to receive logs from a remote client).")
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

    // --- 6.1.3.8 Ensure logrotate is configured ---
    [
        'id' => '6.1.3.8', 'title' => 'Ensure logrotate is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 
  
  if systemctl is-active --quiet systemd-journald.service; then
    a_output_pass+=(" - Journald service is active; skipping check for 'logrotate configuration' as per policy.")
    echo "- Audit Result:"
    echo "  ** SKIPPED ** (Skipped due to journald in use)"
    printf '%s\n' "${a_output_pass[@]}"
    exit 0 
  fi

  l_analyze_cmd="$(readlink -f /bin/systemd-analyze)"
  l_config_file="/etc/logrotate.conf"

  all_logrotate_configs=("$l_config_file")

  l_include_path_raw=$(awk '$1~/^\s*include$/{print$2}' "$l_config_file" 2>/dev/null)

  if [ -n "$l_include_path_raw" ]; then
    if [ -d "$l_include_path_raw" ]; then
      while read -r -d $'\0' f; do
        all_logrotate_configs+=("$(realpath "$f")")
      done < <(find -L "$l_include_path_raw" -maxdepth 1 -type f -name '*.conf' -print0 2>/dev/null)
    elif [ -f "$l_include_path_raw" ]; then
      all_logrotate_configs+=("$(realpath "$l_include_path_raw")")
    fi
  fi

  all_logrotate_configs=($(printf "%s\n" "${all_logrotate_configs[@]}" | sort -u | xargs -r realpath 2>/dev/null))

  if [ "${#all_logrotate_configs[@]}" -eq 0 ]; then
    a_output_fail+=(" - No logrotate configuration files found (e.g., '$l_config_file').")
  else
    LOGROTATE_CONFIG_CONTENT=$(cat "${all_logrotate_configs[@]}" 2>/dev/null)
    if [[ -n "$LOGROTATE_CONFIG_CONTENT" ]] && \
       (echo "$LOGROTATE_CONFIG_CONTENT" | grep -Pq '^\h*(\/var\/log\/|\S+\s+\{|\/|\s*\}\s*$)') ; then
      a_output_pass+=(" - Logrotate configuration files exist and appear to contain rules (correct).")
      a_output_pass+=("   (Manual review of actual logrotate behavior and site policy is required).")
    else
      a_output_fail+=(" - Logrotate configuration files exist but do not appear to contain active rules for log rotation.")
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

    // --- 6.1.4 Configure Logfiles ---
    [
        'id' => '6.1.4', 'title' => 'Configure Logfiles', 'type' => 'header'],

    // --- 6.1.4.1 Ensure access to all logfiles has been configured ---
    [
        'id' => '6.1.4.1', 'title' => 'Ensure access to all logfiles has been configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash 
{
   a_output=(); a_output2=()
   f_file_test_chk()
   {
      a_out2=()
      maxperm="$(printf '%o' $(( 0777 & ~$perm_mask)) )"
      [ $(( $l_mode & $perm_mask )) -gt 0 ] && \
         a_out2+=("   o Mode: \"$l_mode\" should be \"$maxperm\" or more restrictive")
      [[ ! "$l_user" =~ $l_auser ]] && \
         a_out2+=("   o Owned by: \"$l_user\" and should be owned by \"${l_auser//|/ or }\"")
      [[ ! "$l_group" =~ $l_agroup ]] && \
         a_out2+=("   o Group owned by: \"$l_group\" and should be group owned by \"${l_agroup//|/ or }\"")
      [ "${#a_out2[@]}" -gt 0 ] && a_output2+=(" - File: \"$l_fname\" is:" "${a_out2[@]}")
   }
   while IFS= read -r -d $'\0' l_file; do
      while IFS=: read -r l_fname l_mode l_user l_group; do
         if grep -Pq -- '\/(apt)\h*$' <<< "$(dirname "$l_fname")"; then
            perm_mask='0133' l_auser="root" l_agroup="(root|adm)"; f_file_test_chk
         else
            case "$(basename "$l_fname")" in
               lastlog | lastlog.* | wtmp | wtmp.* | wtmp-* | btmp | btmp.* | btmp-* | README)
                  perm_mask='0113' l_auser="root" l_agroup="(root|utmp)"
                  f_file_test_chk ;;
               cloud-init.log* | localmessages* | waagent.log*)
                  perm_mask='0133' l_auser="(root|syslog)" l_agroup="(root|adm)"
                  f_file_test_chk ;;
               secure{,*.*,.*,-*} | auth.log | syslog | messages)
                  perm_mask='0137' l_auser="(root|syslog)" l_agroup="(root|adm)"
                  f_file_test_chk ;;
               SSSD | sssd)
                  perm_mask='0117' l_auser="(root|SSSD)" l_agroup="(root|SSSD)"
                  f_file_test_chk ;;
               gdm | gdm3)
                  perm_mask='0117' l_auser="root" l_agroup="(root|gdm|gdm3)"
                  f_file_test_chk ;;
               *.journal | *.journal~)
                  perm_mask='0137' l_auser="root" l_agroup="(root|systemd-journal)"
                  f_file_test_chk ;; 
               *)
                  perm_mask='0137' l_auser="(root|syslog)" l_agroup="(root|adm)"
                  if [ "$l_user" = "root" ] || ! grep -Pq -- "^\h*$(awk -F: '$1=="'"$l_user"'" {print $7}' /etc/passwd)\b" /etc/shells; then
                     ! grep -Pq -- "$l_auser" <<< "$l_user" && l_auser="(root|syslog|$l_user)"
                     ! grep -Pq -- "$l_agroup" <<< "$l_group" && l_agroup="(root|adm|$l_group)"
                  fi
                  f_file_test_chk ;;
            esac
         fi
      done < <(stat -Lc '%n:%#a:%U:%G' "$l_file")
   done < <(find -L /var/log -type f \( -perm /0137 -o ! -user root -o ! -group root \) -print0)
   if [ "${#a_output2[@]}" -le 0 ]; then
      a_output+=("  - All files in \"/var/log/\" have appropriate permissions and ownership")
      printf '\n%s' "- Audit Result:" "  ** PASS **" "${a_output[@]}" ""
   else
      printf '\n%s' "- Audit Result:" "  ** FAIL **" " - Reason(s) for audit failure:" "${a_output2[@]}" ""
   fi
}
BASH
    ],

    // --- 6.2 System Auditing ---
    [ 'id' => '6.2', 'title' => 'System Auditing', 'type' => 'header' ],

    // --- 6.2.1 Configure auditd Service ---
    [ 'id' => '6.2.1', 'title' => 'Configure auditd Service', 'type' => 'header' ],

    // --- 6.2.1.1 Ensure auditd packages are installed ---
    [
        'id' => '6.2.1.1', 'title' => 'Ensure auditd packages are installed', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  if dpkg-query -s auditd &>/dev/null; then
    a_output_pass+=(" - 'auditd' package is installed (correct).")
  else
    a_output_fail+=(" - 'auditd' package is not installed.")
  fi

  if dpkg-query -s audispd-plugins &>/dev/null; then
    a_output_pass+=(" - 'audispd-plugins' package is installed (correct).")
  else
    a_output_fail+=(" - 'audispd-plugins' package is not installed.")
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

    // --- 6.2.1.2 Ensure auditd service is enabled and active ---
    [
        'id' => '6.2.1.2', 'title' => 'Ensure auditd service is enabled and active', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  AUDITD_ENABLED_STATUS=$(systemctl is-enabled auditd 2>/dev/null)

  if [[ "$AUDITD_ENABLED_STATUS" == "enabled" ]]; then
    a_output_pass+=(" - 'auditd.service' is enabled (correct).")
  else
    a_output_fail+=(" - 'auditd.service' is not enabled. Current status: '$AUDITD_ENABLED_STATUS'.")
  fi

  AUDITD_ACTIVE_STATUS=$(systemctl is-active auditd 2>/dev/null)

  if [[ "$AUDITD_ACTIVE_STATUS" == "active" ]]; then
    a_output_pass+=(" - 'auditd.service' is active (correct).")
  else
    a_output_fail+=(" - 'auditd.service' is not active. Current status: '$AUDITD_ACTIVE_STATUS'.")
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

    // --- 6.2.1.3 Ensure auditing for processes that start prior to auditd is enabled ---
    [
        'id' => '6.2.1.3', 'title' => 'Ensure auditing for processes that start prior to auditd is enabled', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  AUDIT_PARAM_CHECK=$(find /boot -type f -name 'grub.cfg' -exec grep -Ph -- '^\h*linux' {} + 2>/dev/null | grep -v 'audit=1')

  if [[ -z "$AUDIT_PARAM_CHECK" ]]; then
    a_output_pass+=(" - All 'linux' lines in '/boot/grub/grub.cfg' include 'audit=1' (correct).")
  else
    a_output_fail+=(" - Some 'linux' lines in '/boot/grub/grub.cfg' do NOT include 'audit=1':")
    while IFS= read -r line; do
      a_output_fail+=("   $line")
    done <<< "$AUDIT_PARAM_CHECK"
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

    // --- 6.2.1.4 Ensure audit_backlog_limit is sufficient ---
    [
        'id' => '6.2.1.4', 'title' => 'Ensure audit_backlog_limit is sufficient', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  AUDIT_PARAM_CHECK=$(find /boot -type f -name 'grub.cfg' -exec grep -Ph -- '^\h*linux' {} + 2>/dev/null | grep -Pv 'audit_backlog_limit=(819[2-9]|[81][0-9]{4,}|[9-9][0-9]{3,})\b')

  if [[ -z "$AUDIT_PARAM_CHECK" ]]; then
    a_output_pass+=(" - All 'linux' lines in '/boot/grub/grub.cfg' include 'audit_backlog_limit' set to 8192 or larger (correct).")
  else
    a_output_fail+=(" - Some 'linux' lines in '/boot/grub/grub.cfg' do NOT include 'audit_backlog_limit' set to 8192 or larger, or it's missing:")
    while IFS= read -r line; do
      a_output_fail+=("   $line")
    done <<< "$AUDIT_PARAM_CHECK"
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

    // --- 6.2.2 Configure Data Retention ---
    [ 'id' => '6.2.2', 'title' => 'Configure Data Retention', 'type' => 'header' ],

    // --- 6.2.2.1 Ensure audit log storage size is configured ---
    [
        'id' => '6.2.2.1', 'title' => 'Ensure audit log storage size is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
  AUDITD_CONF_FILE="/etc/audit/auditd.conf"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  if [ ! -f "$AUDITD_CONF_FILE" ]; then
    a_output_fail+=(" - Auditd configuration file '$AUDITD_CONF_FILE' does not exist.")
  else
    MAX_LOG_FILE_SETTING=$(grep -Po '^\h*max_log_file\h*=\h*\d+\b' "$AUDITD_CONF_FILE")

    if [[ -n "$MAX_LOG_FILE_SETTING" ]]; then
      LOG_SIZE_MB=$(echo "$MAX_LOG_FILE_SETTING" | awk -F'=' '{print $2}' | tr -d '[:space:]')
      a_output_pass+=(" - 'max_log_file' is configured to '$LOG_SIZE_MB' MB in '$AUDITD_CONF_FILE'. (Manual review needed for site policy compliance).")
    else
      a_output_fail+=(" - 'max_log_file' setting is not found or is commented out in '$AUDITD_CONF_FILE'.")
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

    // --- 6.2.2.2 Ensure audit logs are not automatically deleted ---
    [
        'id' => '6.2.2.2', 'title' => 'Ensure audit logs are not automatically deleted', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  AUDITD_CONF_FILE="/etc/audit/auditd.conf"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  if [ ! -f "$AUDITD_CONF_FILE" ]; then
    a_output_fail+=(" - Auditd configuration file '$AUDITD_CONF_FILE' does not exist.")
  else
    MAX_LOG_FILE_ACTION_SETTING=$(grep -P '^\h*max_log_file_action\h*=\h*\S+\b' "$AUDITD_CONF_FILE" | tail -n 1)
    
    if [[ "$MAX_LOG_FILE_ACTION_SETTING" == "max_log_file_action = keep_logs" ]]; then
      a_output_pass+=(" - 'max_log_file_action' in '$AUDITD_CONF_FILE' is set to 'keep_logs' (correct).")
    else
      if [[ -n "$MAX_LOG_FILE_ACTION_SETTING" ]]; then
        a_output_fail+=(" - 'max_log_file_action' in '$AUDITD_CONF_FILE' is set to an unexpected value: '$MAX_LOG_FILE_ACTION_SETTING'. It should be 'max_log_file_action = keep_logs'.")
      else
        a_output_fail+=(" - 'max_log_file_action' setting is not found or is commented out in '$AUDITD_CONF_FILE'. It should be 'max_log_file_action = keep_logs'.")
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

    // --- 6.2.2.3 Ensure system is disabled when audit logs are full ---
    [
        'id' => '6.2.2.3', 'title' => 'Ensure system is disabled when audit logs are full', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  AUDITD_CONF_FILE="/etc/audit/auditd.conf"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  if [ ! -f "$AUDITD_CONF_FILE" ]; then
    a_output_fail+=(" - Auditd configuration file '$AUDITD_CONF_FILE' does not exist.")
  else
    DISK_FULL_ACTION_SETTING=$(grep -Pi -- '^\h*disk_full_action\h*=\h*(halt|single)\b' "$AUDITD_CONF_FILE" | tail -n 1)

    if [[ -n "$DISK_FULL_ACTION_SETTING" ]]; then
      a_output_pass+=(" - 'disk_full_action' in '$AUDITD_CONF_FILE' is set to '$DISK_FULL_ACTION_SETTING' (correct, 'halt' or 'single').")
    else
      CURRENT_DISK_FULL_ACTION=$(grep -Pi -- '^\h*disk_full_action\h*=\h*\S+\b' "$AUDITD_CONF_FILE" | tail -n 1)
      if [[ -n "$CURRENT_DISK_FULL_ACTION" ]]; then
        a_output_fail+=(" - 'disk_full_action' in '$AUDITD_CONF_FILE' is set to an unexpected value: '$CURRENT_DISK_FULL_ACTION'. It should be 'halt' or 'single'.")
      else
        a_output_fail+=(" - 'disk_full_action' setting is not found or is commented out in '$AUDITD_CONF_FILE'. It should be 'halt' or 'single'.")
      fi
    fi

    DISK_ERROR_ACTION_SETTING=$(grep -Pi -- '^\h*disk_error_action\h*=\h*(syslog|single|halt)\b' "$AUDITD_CONF_FILE" | tail -n 1)

    if [[ -n "$DISK_ERROR_ACTION_SETTING" ]]; then
      a_output_pass+=(" - 'disk_error_action' in '$AUDITD_CONF_FILE' is set to '$DISK_ERROR_ACTION_SETTING' (correct, 'syslog', 'single', or 'halt').")
    else
      CURRENT_DISK_ERROR_ACTION=$(grep -Pi -- '^\h*disk_error_action\h*=\h*\S+\b' "$AUDITD_CONF_FILE" | tail -n 1)
      if [[ -n "$CURRENT_DISK_ERROR_ACTION" ]]; then
        a_output_fail+=(" - 'disk_error_action' in '$AUDITD_CONF_FILE' is set to an unexpected value: '$CURRENT_DISK_ERROR_ACTION'. It should be 'syslog', 'single', or 'halt'.")
      else
        a_output_fail+=(" - 'disk_error_action' setting is not found or is commented out in '$AUDITD_CONF_FILE'. It should be 'syslog', 'single', or 'halt'.")
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

    // --- 6.2.2.4 Ensure system warns when audit logs are low on space ---
    [
        'id' => '6.2.2.4', 'title' => 'Ensure system warns when audit logs are low on space', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  AUDITD_CONF_FILE="/etc/audit/auditd.conf"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  if [ ! -f "$AUDITD_CONF_FILE" ]; then
    a_output_fail+=(" - Auditd configuration file '$AUDITD_CONF_FILE' does not exist.")
  else
    SPACE_LEFT_ACTION_SETTING=$(grep -Pi -- '^\h*space_left_action\h*=\h*(email|exec|single|halt)\b' "$AUDITD_CONF_FILE" | tail -n 1)

    if [[ -n "$SPACE_LEFT_ACTION_SETTING" ]]; then
      a_output_pass+=(" - 'space_left_action' in '$AUDITD_CONF_FILE' is set to '$SPACE_LEFT_ACTION_SETTING' (correct, 'email', 'exec', 'single', or 'halt').")
    else
      CURRENT_SPACE_LEFT_ACTION=$(grep -Pi -- '^\h*space_left_action\h*=\h*\S+\b' "$AUDITD_CONF_FILE" | tail -n 1)
      if [[ -n "$CURRENT_SPACE_LEFT_ACTION" ]]; then
        a_output_fail+=(" - 'space_left_action' in '$AUDITD_CONF_FILE' is set to an unexpected value: '$CURRENT_SPACE_LEFT_ACTION'. It should be 'email', 'exec', 'single', or 'halt'.")
      else
        a_output_fail+=(" - 'space_left_action' setting is not found or is commented out in '$AUDITD_CONF_FILE'. It should be 'email', 'exec', 'single', or 'halt'.")
      fi
    fi

    ADMIN_SPACE_LEFT_ACTION_SETTING=$(grep -Pi -- '^\h*admin_space_left_action\h*=\h*(single|halt)\b' "$AUDITD_CONF_FILE" | tail -n 1)

    if [[ -n "$ADMIN_SPACE_LEFT_ACTION_SETTING" ]]; then
      a_output_pass+=(" - 'admin_space_left_action' in '$AUDITD_CONF_FILE' is set to '$ADMIN_SPACE_LEFT_ACTION_SETTING' (correct, 'single' or 'halt').")
    else
      CURRENT_ADMIN_SPACE_LEFT_ACTION=$(grep -Pi -- '^\h*admin_space_left_action\h*=\h*\S+\b' "$AUDITD_CONF_FILE" | tail -n 1)
      if [[ -n "$CURRENT_ADMIN_SPACE_LEFT_ACTION" ]]; then
        a_output_fail+=(" - 'admin_space_left_action' in '$AUDITD_CONF_FILE' is set to an unexpected value: '$CURRENT_ADMIN_SPACE_LEFT_ACTION'. It should be 'single' or 'halt'.")
      else
        a_output_fail+=(" - 'admin_space_left_action' setting is not found or is commented out in '$AUDITD_CONF_FILE'. It should be 'single' or 'halt'.")
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

    // --- 6.2.3 Configure auditd Rules ---
    [ 'id' => '6.2.3', 'title' => 'Configure auditd Rules', 'type' => 'header' ],

    // --- 6.2.3.1 Ensure changes to system administration scope (sudoers) is collected ---
    [
        'id' => '6.2.3.1', 'title' => 'Ensure changes to system administration scope (sudoers) is collected', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  AUDIT_RULES_DIR="/etc/audit/rules.d"
  SUDOERS_FILE="/etc/sudoers"
  SUDOERS_D_DIR="/etc/sudoers.d"

  EXPECTED_RULE_SUDOERS="-w ${SUDOERS_FILE} -p wa -k scope"
  EXPECTED_RULE_SUDOERS_D="-w ${SUDOERS_D_DIR} -p wa -k scope"

  ON_DISK_SUDOERS=$(awk '/^ *-w/ && /\/etc\/sudoers/ && / +-p *wa/ && (/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)' "${AUDIT_RULES_DIR}"/*.rules 2>/dev/null)
  ON_DISK_SUDOERS_D=$(awk '/^ *-w/ && /\/etc\/sudoers.d/ && / +-p *wa/ && (/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)' "${AUDIT_RULES_DIR}"/*.rules 2>/dev/null)

  if [[ -n "$ON_DISK_SUDOERS" && -n "$ON_DISK_SUDOERS_D" ]]; then
    a_output_pass+=(" - On-disk audit rules for '${SUDOERS_FILE}' and '${SUDOERS_D_DIR}' are present (correct).")
  else
    a_output_fail+=(" - On-disk audit rules for '${SUDOERS_FILE}' or '${SUDOERS_D_DIR}' are missing or incorrectly configured.")
  fi

  RUNNING_SUDOERS=$(auditctl -l 2>/dev/null | awk '/^ *-w/ && /\/etc\/sudoers/ && / +-p *wa/ && (/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)')
  RUNNING_SUDOERS_D=$(auditctl -l 2>/dev/null | awk '/^ *-w/ && /\/etc\/sudoers.d/ && / +-p *wa/ && (/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)')

  if [[ -n "$RUNNING_SUDOERS" && -n "$RUNNING_SUDOERS_D" ]]; then
    a_output_pass+=(" - Running audit rules for '${SUDOERS_FILE}' and '${SUDOERS_D_DIR}' are loaded (correct).")
  else
    a_output_fail+=(" - Running audit rules for '${SUDOERS_FILE}' or '${SUDOERS_D_DIR}' are missing or incorrectly loaded.")
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

    // --- 6.2.3.2 Ensure actions as another user are always logged ---
    [
        'id' => '6.2.3.2', 'title' => 'Ensure actions as another user are always logged', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  AUDIT_RULES_DIR="/etc/audit/rules.d"

  EXPECTED_RULE_64_PART1=" -a always,exit -F arch=b64"
  EXPECTED_RULE_64_PART2_UID=" -C euid!=uid"
  EXPECTED_RULE_64_PART3_AUID=" -F auid!=unset"
  EXPECTED_RULE_64_PART4_SYSCALL=" -S execve"
  EXPECTED_RULE_64_PART5_KEY=" -k user_emulation"

  EXPECTED_RULE_32_PART1=" -a always,exit -F arch=b32"
  EXPECTED_RULE_32_PART2_UID=" -C euid!=uid"
  EXPECTED_RULE_32_PART3_AUID=" -F auid!=unset" 
  EXPECTED_RULE_32_PART4_SYSCALL=" -S execve"
  EXPECTED_RULE_32_PART5_KEY=" -k user_emulation"

  ON_DISK_RULE_64_EXISTS=$(grep -Prsil -- "${EXPECTED_RULE_64_PART1}.*(-C *euid!=uid|-C *uid!=euid).*(-F *auid!=unset|-F *auid!=-1|-F *auid!=4294967295).*${EXPECTED_RULE_64_PART4_SYSCALL}.*${EXPECTED_RULE_64_PART5_KEY// / *}" "${AUDIT_RULES_DIR}"/*.rules 2>/dev/null)
  ON_DISK_RULE_32_EXISTS=$(grep -Prsil -- "${EXPECTED_RULE_32_PART1}.*(-C *euid!=uid|-C *uid!=euid).*(-F *auid!=unset|-F *auid!=-1|-F *auid!=4294967295).*${EXPECTED_RULE_32_PART4_SYSCALL}.*${EXPECTED_RULE_32_PART5_KEY// / *}" "${AUDIT_RULES_DIR}"/*.rules 2>/dev/null)

  if [[ -n "$ON_DISK_RULE_64_EXISTS" && -n "$ON_DISK_RULE_32_EXISTS" ]]; then
    a_output_pass+=(" - On-disk audit rules for 'user_emulation' (b64 and b32) are present (correct).")
  else
    a_output_fail+=(" - On-disk audit rules for 'user_emulation' (b64 and/or b32) are missing or incorrectly configured.")
  fi

  RUNNING_RULES=$(auditctl -l 2>/dev/null)

  RUNNING_RULE_64_CHECK=$(echo "$RUNNING_RULES" | awk '/^ *-a *always,exit/ && / -F *arch=b64/ && (/ -F *auid!=unset/||/ -F *auid!=-1/||/ -F *auid!=4294967295/) && (/ -C *euid!=uid/||/ -C *uid!=euid/) && / -S *execve/ && (/ key= *user_emulation *$/||/ -k *user_emulation *$/)')

  RUNNING_RULE_32_CHECK=$(echo "$RUNNING_RULES" | awk '/^ *-a *always,exit/ && / -F *arch=b32/ && (/ -F *auid!=unset/||/ -F *auid!=-1/||/ -F *auid!=4294967295/) && (/ -C *euid!=uid/||/ -C *uid!=euid/) && / -S *execve/ && (/ key= *user_emulation *$/||/ -k *user_emulation *$/)')

  if [[ -n "$RUNNING_RULE_64_CHECK" && -n "$RUNNING_RULE_32_CHECK" ]]; then
    a_output_pass+=(" - Running audit rules for 'user_emulation' (b64 and b32) are loaded (correct).")
  else
    a_output_fail+=(" - Running audit rules for 'user_emulation' (b64 and/or b32) are missing or incorrectly loaded.")
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

    // --- 6.2.3.3 Ensure events that modify the sudo log file are collected ---
    [
        'id' => '6.2.3.3', 'title' => 'Ensure events that modify the sudo log file are collected', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  AUDIT_RULES_DIR="/etc/audit/rules.d"

  SUDO_LOG_FILE_PATH=$(grep -r logfile /etc/sudoers* 2>/dev/null | sed -e 's/.*logfile=//;s/,?.*//' -e 's/"//g' | head -n 1)
  ESCAPED_SUDO_LOG_FILE_PATH=$(echo "$SUDO_LOG_FILE_PATH" | sed 's/\//\\\//g')

  if [[ -z "$SUDO_LOG_FILE_PATH" ]]; then
    a_output_fail+=(" - SUDO log file path is not configured in '/etc/sudoers' or '/etc/sudoers.d/'. Cannot perform audit.")
  else
    EXPECTED_RULE_PATTERN="^ *-w .*${ESCAPED_SUDO_LOG_FILE_PATH}.* +-p *wa *(key= *sudo_log_file| -k *sudo_log_file *\$)"

    ON_DISK_RULE=$(awk '/^ *-w/ && /\/etc\/sudoers/ && / +-p *wa/ && (/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)' "${AUDIT_RULES_DIR}"/*.rules 2>/dev/null | grep -P -- "${ESCAPED_SUDO_LOG_FILE_PATH}.*")
    
    if echo "$ON_DISK_RULE" | grep -Pq "$EXPECTED_RULE_PATTERN"; then
      a_output_pass+=(" - On-disk audit rule for sudo log file ('${SUDO_LOG_FILE_PATH}') is present and correct.")
    else
      a_output_fail+=(" - On-disk audit rule for sudo log file ('${SUDO_LOG_FILE_PATH}') is missing or incorrect.")
    fi

    RUNNING_RULE=$(auditctl -l 2>/dev/null | awk '/^ *-w/ && /\/etc\/sudoers/ && / +-p *wa/ && (/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)' | grep -P -- "${ESCAPED_SUDO_LOG_FILE_PATH}.*")

    if echo "$RUNNING_RULE" | grep -Pq "$EXPECTED_RULE_PATTERN"; then
      a_output_pass+=(" - Running audit rule for sudo log file ('${SUDO_LOG_FILE_PATH}') is loaded and correct.")
    else
      a_output_fail+=(" - Running audit rule for sudo log file ('${SUDO_LOG_FILE_PATH}') is missing or incorrectly loaded.")
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

    // --- 6.2.3.4 Ensure events that modify date and time information are collected ---
    [
        'id' => '6.2.3.4', 'title' => 'Ensure events that modify date and time information are collected', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  a_output_suggestion=()

  echo "- Audit Result: ** MANUAL **"

  a_output_suggestion+=(
"###########################################################################################"
"# 1. To audit on-disk configuration, run the following script  to check the on disk rules:"
"###########################################################################################"
"# {" 
" awk '/^ *-a *always,exit/ &&/ -F *arch=b(32|64)/ &&/ -S/ &&(/adjtimex/ ||/settimeofday/ ||/clock_settime/ ) (/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)' /etc/audit/rules.d/*.rules"
" awk '/^ *-w/ &&/\/etc\/localtime/ &&/ +-p *wa/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)' /etc/audit/rules.d/*.rules"
"}"
""
"then, Verify output of matches: "
" -a always,exit -F arch=b64 -S adjtimex,settimeofday -k time-change" 
" -a always,exit -F arch=b32 -S adjtimex,settimeofday -k time-change"
" -a always,exit -F arch=b64 -S clock_settime -F a0=0x0 -k time-change" 
" -a always,exit -F arch=b32 -S clock_settime -F a0=0x0 -k time-change" 
" -w /etc/localtime -p wa -k time-change"
""
"###########################################################################################"
"# 2. To audit running configuration, run the following script to check loaded rules::"
"###########################################################################################"
"# {" 
" auditctl -l | awk '/^ *-a *always,exit/ &&/ -F *arch=b(32|64)/ &&/ -S/ &&(/adjtimex/ ||/settimeofday/ ||/clock_settime/ ) &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)'"
" auditctl -l | awk '/^ *-w/ &&/\/etc\/localtime/ &&/ +-p *wa/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)'"
"}"
""
"Verify the output includes: "
" -a always,exit -F arch=b64 -S adjtimex,settimeofday -F key=time-change"
" -a always,exit -F arch=b32 -S settimeofday,adjtimex -F key=time-change"
" -a always,exit -F arch=b64 -S clock_settime -F a0=0x0 -F key=time-change"
" -a always,exit -F arch=b32 -S clock_settime -F a0=0x0 -F key=time-change" 
" -w /etc/localtime -p wa -k time-cha"
""
)

  if [ "${#a_output_suggestion[@]}" -gt 0 ]; then
    echo ""
    echo "-- Suggestion --"
    printf '%s\n' "${a_output_suggestion[@]}"
  fi
}
BASH
    ],

    // --- 6.2.3.5 Ensure events that modify the system's network environment are collected ---
    [
        'id' => '6.2.3.5', 'title' => 'Ensure events that modify the system\'s network environment are collected', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#\!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  a_output_suggestion=()
  AUDIT_OVERALL_STATUS="MANUAL"

  echo "- Audit Result: ** MANUAL **"

  a_output_suggestion+=(
"###########################################################################################"
"# 1. To audit on-disk configuration, run the following script  to check the on disk rules:"
"###########################################################################################"
"# awk '/^ *-a *always,exit/ \&\&/ -F *arch=b(32\|64)/ \&\&/ -S/ \&\&(/sethostname/ \|\|/setdomainname/) \&\&(/ key= *[\!-~]* *$/\|\|/ -k *[\!-~]* *$/)' /etc/audit/rules.d/*.rules"
"# awk '/^ *-w/ \&\&(/\/etc\/issue/ \|\|/\/etc\/issue.net/ \|\|/\/etc\/hosts/ \|\|/\/etc\/network/ \|\|/\/etc\/netplan/) \&\&/ +-p *wa/ \&\&(/ key= *[\!-~]* *$/\|\|/ -k *[\!-~]* *$/)' /etc/audit/rules.d/*.rules"
""
"then, verify output of matches: "
" -a always,exit -F arch=b64 -S sethostname,setdomainname -k system-locale" 
" -a always,exit -F arch=b32 -S sethostname,setdomainname -k system-locale"
" -w /etc/issue -p wa -k system-locale"
" -w /etc/issue.net -p wa -k system-locale"
" -w /etc/hosts -p wa -k system-locale"
" -w /etc/networks -p wa -k system-locale"
" -w /etc/network -p wa -k system-locale"
" -w /etc/netplan -p wa -k system-local"
""

"###########################################################################################"
"# 2. To audit running configuration, run the following script to check loaded rules:"
"###########################################################################################"
"# auditctl -l \| awk '/^ *-a *always,exit/ \&\&/ -F *arch=b(32\|64)/ \&\&/ -S/ \&\&(/sethostname/ \|\|/setdomainname/) \&\&(/ key= *[\!-~]* *$/\|\|/ -k *[\!-~]* *$/)'" 

"# auditctl -l \| awk '/^ *-w/ \&\&(/\/etc\/issue/ \|\|/\/etc\/issue.net/ \|\|/\/etc\/hosts/ \|\|/\/etc\/network/ \|\|/\/etc\/netplan/) \&\&/ +-p *wa/ \&\&(/ key= *[\!-~]* *$/\|\|/ -k *[\!-~]* *$/)'"

""
"then, verify the output includes: "
" -a always,exit -F arch=b64 -S sethostname,setdomainname -F key=system-locale"
" -a always,exit -F arch=b32 -S sethostname,setdomainname -F key=system-locale"
" -w /etc/issue -p wa -k system-locale"
" -w /etc/issue -p wa -k system-locale"
" -w /etc/issue.net -p wa -k system-locale"
" -w /etc/hosts -p wa -k system-locale"
" -w /etc/networks -p wa -k system-locale"
" -w /etc/network -p wa -k system-locale"
" -w /etc/netplan -p wa -k system-locale"

""
  )
  
  if [ "${#a_output_suggestion[@]}" -gt 0 ]; then
    echo ""
    echo "-- Suggestion --"
    printf '%s\n' "${a_output_suggestion[@]}"
  fi
}
BASH
    ],

    // --- 6.2.3.6 Ensure use of privileged commands are collected ---
    [
        'id' => '6.2.3.6', 'title' => 'Ensure use of privileged commands are collected', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  a_output_suggestion=()
  AUDIT_OVERALL_STATUS="MANUAL"

  echo "- Audit Result: ** MANUAL **"

  a_output_suggestion+=(
"###########################################################################################"
"# 1. To audit on-disk configuration, run the following script:"
"###########################################################################################"
'#!/usr/bin/env bash'
"{"
"  for PARTITION in \$(findmnt -n -l -k -it \$(awk '/nodev/ { print \$2 }' /proc/filesystems | paste -sd,) | grep -Pv \"noexec|nosuid\" | awk '{print \$1}'); do"
"    for PRIVILEGED in \$(find \"\${PARTITION}\" -xdev -perm /6000 -type f); do"
"      grep -qr \"\${PRIVILEGED}\" /etc/audit/rules.d && printf \"OK: '\${PRIVILEGED}' found in auditing rules.\\n\" || printf \"Warning: '\${PRIVILEGED}' not found in on disk configuration.\\n\""
"    done"
"  done"
"}"
""
"then, Verify that all output is OK."
""
"###########################################################################################"
"# 2. To audit running configuration, run the following script:"
"###########################################################################################"
'#!/usr/bin/env bash'
"{"
"  RUNNING=\$(auditctl -l)"
"  [ -n \"\${RUNNING}\" ] && for PARTITION in \$(findmnt -n -l -k -it \$(awk '/nodev/ { print \$2 }' /proc/filesystems | paste -sd,) | grep -Pv \"noexec|nosuid\" | awk '{print \$1}'); do"
"    for PRIVILEGED in \$(find \"\${PARTITION}\" -xdev -perm /6000 -type f); do"
"      printf -- \"\${RUNNING}\" | grep -q \"\${PRIVILEGED}\" && printf \"OK: '\${PRIVILEGED}' found in auditing rules.\\n\" || printf \"Warning: '\${PRIVILEGED}' not found in running configuration.\\n\""
"    done"
"  done \\"
"  || printf \"ERROR: Variable 'RUNNING' is unset.\\n\""
"}"
""
"then, Verify that all output is OK."
)

  if [ "${#a_output_suggestion[@]}" -gt 0 ]; then
    echo ""
    echo "-- Suggestion --"
    printf '%s\n' "${a_output_suggestion[@]}"
  fi
}
BASH
    ],

    // --- 6.2.3.7 Ensure unsuccessful file access attempts are collected ---
    [
        'id' => '6.2.3.7', 'title' => 'Ensure unsuccessful file access attempts are collected', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#\!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  a_output_suggestion=()
  AUDIT_OVERALL_STATUS="MANUAL"

  echo "- Audit Result: ** MANUAL **"

  a_output_suggestion+=(
"###########################################################################################"
"# 1. To audit on-disk configuration, run the following script  to check the on disk rules:"
"###########################################################################################"
"# {"
" UID_MIN=\$(awk '/^\s*UID_MIN/{print $2}' /etc/login.defs)"
" [ -n \"\${UID_MIN}\" ] && awk \"/^ *-a *always,exit/ &&/ -F *arch=b(32|64)/ &&/ -F *arch=b(32|64)/ &&(/ -F *auid!=unset/||/ -F *auid!=-1/||/ -F *auid!=4294967295/) &&/ -F *auid>=\${UID_MIN}/ &&(/ -F *exit=-EACCES/||/ -F *exit=-EPERM/) &&/ -S/ &&/creat/ &&/open/ &&/truncate/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)\" /etc/audit/rules.d/*.rules || printf \"ERROR: Variable 'UID_MIN' is unset.\n\""
""
"Verify the output includes:"
" -a always,exit -F arch=b64 -S creat,open,openat,truncate,ftruncate -F exit=EACCES -F auid>=1000 -F auid!=unset -k access"
" -a always,exit -F arch=b64 -S creat,open,openat,truncate,ftruncate -F exit=EPERM -F auid>=1000 -F auid!=unset -k access"
" -a always,exit -F arch=b32 -S creat,open,openat,truncate,ftruncate -F exit=EACCES -F auid>=1000 -F auid!=unset -k access"
" -a always,exit -F arch=b32 -S creat,open,openat,truncate,ftruncate -F exit=EPERM -F auid>=1000 -F auid!=unset -k access"
""
"###########################################################################################"
"# 2. To audit running configuration, run the following script to check loaded rules:"
"###########################################################################################"
"# {"
" UID_MIN=\$(awk '/^\s*UID_MIN/{print \$2}' /etc/login.defs)"
" [ -n \"\${UID_MIN}\" ] && auditctl -l | awk \"/^ *-a *always,exit/ &&/ -F *arch=b(32|64)/ &&(/ -F *auid!=unset/||/ -F  *auid!=-1/||/ -F *auid!=4294967295/) &&/ -F *auid>=\${UID_MIN}/ &&(/ -F *exit=-EACCES/||/ -F *exit=-EPERM/) &&/ -S/  &&/creat/ &&/open/ &&/truncate/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)\" || printf \"ERROR: Variable 'UID_MIN' is unset.\n\"" 
"}"
""
"Verify the output includes:"
"-a always,exit -F arch=b64 -S open,truncate,ftruncate,creat,openat -F exit=EACCES -F auid>=1000 -F auid!=-1 -F key=access"
" -a always,exit -F arch=b64 -S open,truncate,ftruncate,creat,openat -F exit=EPERM -F auid>=1000 -F auid!=-1 -F key=access"
" -a always,exit -F arch=b32 -S open,truncate,ftruncate,creat,openat -F exit=EACCES -F auid>=1000 -F auid!=-1 -F key=access"
" -a always,exit -F arch=b32 -S open,truncate,ftruncate,creat,openat -F exit=EPERM -F auid>=1000 -F auid!=-1 -F key=access"
""
)
  if [ "${#a_output_suggestion[@]}" -gt 0 ]; then
    echo ""
    echo "-- Suggestion --"
    printf '%s\n' "${a_output_suggestion[@]}"
  fi
}

BASH
    ],

    // --- 6.2.3.8 Ensure events that modify user/group information are collected ---
    [
        'id' => '6.2.3.8', 'title' => 'Ensure events that modify user/group information are collected', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#\!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  a_output_suggestion=()
  AUDIT_OVERALL_STATUS="MANUAL"

  echo "- Audit Result: ** MANUAL **"

  a_output_suggestion+=(
"###########################################################################################"
"# 1. To audit on-disk configuration, run the following script  to check the on disk rules:"
"###########################################################################################"
"# awk '/^ *-w/ &&(/\/etc\/group/ ||/\/etc\/passwd/ ||/\/etc\/gshadow/ ||/\/etc\/shadow/ ||/\/etc\/security\/opasswd/ ||/\/etc\/nsswitch.conf/ ||/\/etc\/pam.conf/ ||/\/etc\/pam.d/) &&/ +-p *wa/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)' /etc/audit/rules.d/*.rules"
""
"Verify the output matches:"
"-w /etc/group -p wa -k identity"
" -w /etc/passwd -p wa -k identity"
" -w /etc/gshadow -p wa -k identity"
" -w /etc/shadow -p wa -k identity"
" -w /etc/security/opasswd -p wa -k identity"
" -w /etc/nsswitch.conf -p wa -k identity"
" -w /etc/pam.conf -p wa -k identity"
" -w /etc/pam.d -p wa -k identity "
""
"###########################################################################################"
"# 2. To audit running configuration, run the following script to check loaded rules:"
"###########################################################################################"
"# auditctl -l | awk '/^ *-w/ &&(/\/etc\/group/ ||/\/etc\/passwd/ ||/\/etc\/gshadow/ ||/\/etc\/shadow/ ||/\/etc\/security\/opasswd/ ||/\/etc\/nsswitch.conf/ ||/\/etc\/pam.conf/ ||/\/etc\/pam.d/) &&/ +-p *wa/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)' "
""
"Verify the output matches:"
"-w /etc/group -p wa -k identity"
" -w /etc/passwd -p wa -k identity"
" -w /etc/gshadow -p wa -k identity"
" -w /etc/shadow -p wa -k identity"
" -w /etc/security/opasswd -p wa -k identity"
" -w /etc/nsswitch.conf -p wa -k identity"
" -w /etc/pam.conf -p wa -k identity"
" -w /etc/pam.d -p wa -k identity"
""
)
  if [ "${#a_output_suggestion[@]}" -gt 0 ]; then
    echo ""
    echo "-- Suggestion --"
    printf '%s\n' "${a_output_suggestion[@]}"
  fi
}

BASH
    ],

    // --- 6.2.3.9 Ensure discretionary access control permission modification events are collected ---
    [
        'id' => '6.2.3.9', 'title' => 'Ensure discretionary access control permission modification events are collected', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#\!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  a_output_suggestion=()
  AUDIT_OVERALL_STATUS="MANUAL"

  echo "- Audit Result: ** MANUAL **"

  a_output_suggestion+=(
"###########################################################################################"
"# 1. To audit on-disk configuration, run the following script  to check the on disk rules:"
"###########################################################################################"
"# {"
" UID_MIN=\$(awk '/^\s*UID_MIN/{print \$2}' /etc/login.defs)" 
" [ -n \"\${UID_MIN}\" ] && awk \"/^ *-a *always,exit/  &&/ -F *arch=b(32|64)/ &&(/ -F *auid!=unset/||/ -F *auid!=-1/||/ -F *auid!=4294967295/) &&/ -S/ &&/ -F *auid>=\${UID_MIN}/ &&(/chmod/||/fchmod/||/fchmodat/||/chown/||/fchown/||/fchownat/||/setxattr/||/lsetxattr/||/fsetxattr/||/lchown/||/removexattr/||/lremovexattr/||/fremovexattr/) &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)\" /etc/audit/rules.d/*.rules|| printf \"ERROR: Variable 'UID_MIN' is unset.\n\""
"}"
""
"Verify the output matches: "
"-a always,exit -F arch=b64 -S chmod,fchmod,fchmodat -F auid>=1000 -F auid!=unset -F key=perm_mod"
" -a always,exit -F arch=b64 -S chown,fchown,lchown,fchownat -F auid>=1000 -F auid!=unset -F key=perm_mod"
" -a always,exit -F arch=b32 -S chmod,fchmod,fchmodat -F auid>=1000 -F auid!=unset -F key=perm_mod"
" -a always,exit -F arch=b32 -S lchown,fchown,chown,fchownat -F auid>=1000 -F auid!=unset -F key=perm_mod"
" -a always,exit -F arch=b64 -S setxattr,lsetxattr,fsetxattr,removexattr,lremovexattr,fremovexattr -F auid>=1000 -F auid!=unset -F key=perm_mod"
" -a always,exit -F arch=b32 -S setxattr,lsetxattr,fsetxattr,removexattr,lremovexattr,fremovexattr -F 
auid>=1000 -F auid!=unset -F key=perm_mod"
""
"###########################################################################################"
"# 2. To audit running configuration, run the following script to check loaded rules:"
"###########################################################################################"
"# {"
" UID_MIN=\$(awk '/^\s*UID_MIN/{print \$2}' /etc/login.defs)"
" [ -n \"\${UID_MIN}\" ] && auditctl -l | awk \"/^ *-a *always,exit/ &&/ -F *arch=b(32|64)/ &&(/ -F *auid!=unset/||/ -F *auid!=-1/||/ -F *auid!=4294967295/) &&/ -S/ &&/ -F *auid>=\${UID_MIN}/ &&(/chmod/||/fchmod/||/fchmodat/||/chown/||/fchown/||/fchownat/||/lchown/||/setxattr/||/lsetxattr/||/fsetxattr/||/removexattr/||/lremovexattr/||/fremovexattr/) &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)\" || printf \"ERROR: Variable 'UID_MIN' is unset.\n\""
"}"
""
"Verify the output matches:"
"-a always,exit -F arch=b64 -S chmod,fchmod,fchmodat -F auid>=1000 -F auid!=-1 -F key=perm_mod"
" -a always,exit -F arch=b64 -S chown,fchown,lchown,fchownat -F auid>=1000 -F auid!=-1 -F key=perm_mod"
" -a always,exit -F arch=b32 -S chmod,fchmod,fchmodat -F auid>=1000 -F auid!=-1 -F key=perm_mod"
" -a always,exit -F arch=b32 -S lchown,fchown,chown,fchownat -F auid>=1000 -F auid!=-1 -F key=perm_mod"
" -a always,exit -F arch=b64 -S setxattr,lsetxattr,fsetxattr,removexattr,lremovexattr,fremovexattr -F auid>=1000 -F auid!=-1 -F key=perm_mod"
" -a always,exit -F arch=b32 -S setxattr,lsetxattr,fsetxattr,removexattr,lremovexattr,fremovexattr -F auid>=1000 -F auid!=-1 -F key=perm_mod"
""
)
  if [ "${#a_output_suggestion[@]}" -gt 0 ]; then
    echo ""
    echo "-- Suggestion --"
    printf '%s\n' "${a_output_suggestion[@]}"
  fi
}
BASH
    ],

    // --- 6.2.3.10 Ensure successful file system mounts are collected ---
    [
        'id' => '6.2.3.10', 'title' => 'Ensure successful file system mounts are collected', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#\!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  a_output_suggestion=()
  AUDIT_OVERALL_STATUS="MANUAL"

  echo "- Audit Result: ** MANUAL **"

  a_output_suggestion+=(
"###########################################################################################"
"# 1. To audit on-disk configuration, run the following script  to check the on disk rules:"
"###########################################################################################"
"# {"
" UID_MIN=\$(awk '/^\s*UID_MIN/{print \$2}' /etc/login.defs)" 
" [ -n \"\${UID_MIN}\" ] && awk \"/^ *-a *always,exit/ &&/ -F *arch=b(32|64)/ &&(/ -F *auid!=unset/||/ -F *auid!=-1/||/ -F *auid!=4294967295/) &&/ -F *auid>=\${UID_MIN}/ &&/ -S/ &&/mount/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)\" /etc/audit/rules.d/*.rules || printf \"ERROR: Variable 'UID_MIN' is unset.\n\"" 
"}"
""
"Verify the output matches:"
" -a always,exit -F arch=b64 -S mount -F auid>=1000 -F auid!=unset -k mounts"
" -a always,exit -F arch=b32 -S mount -F auid>=1000 -F auid!=unset"
""
"###########################################################################################"
"# 2. To audit running configuration, run the following script to check loaded rules:"
"###########################################################################################"
"# {"
" UID_MIN=\$(awk '/^\s*UID_MIN/{print \$2}' /etc/login.defs)" 
" [ -n \"\${UID_MIN}\" ] && auditctl -l | awk \"/^ *-a *always,exit/ &&/ -F *arch=b(32|64)/ &&(/ -F *auid!=unset/||/ -F *auid!=-1/||/ -F *auid!=4294967295/) &&/ -F *auid>=\${UID_MIN}/ &&/ -S/ &&/mount/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)\" || printf \"ERROR: Variable 'UID_MIN' is unset.\n\"" 
"}"
""
"Verify the output matches:"
" -a always,exit -F arch=b64 -S mount -F auid>=1000 -F auid!=-1 -F key=mounts"
" -a always,exit -F arch=b32 -S mount -F auid>=1000 -F auid!=-1 -F key=mounts"
""
)
  if [ "${#a_output_suggestion[@]}" -gt 0 ]; then
    echo ""
    echo "-- Suggestion --"
    printf '%s\n' "${a_output_suggestion[@]}"
  fi
}
BASH
    ],

    // --- 6.2.3.11 Ensure session initiation information is collected ---
    [
        'id' => '6.2.3.11', 'title' => 'Ensure session initiation information is collected', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#\!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  a_output_suggestion=()
  AUDIT_OVERALL_STATUS="MANUAL"

  echo "- Audit Result: ** MANUAL **"

  a_output_suggestion+=(
"###########################################################################################"
"# 1. To audit on-disk configuration, run the following script  to check the on disk rules:"
"###########################################################################################"
"# awk '/^ *-w/ &&(/\/var\/run\/utmp/||/\/var\/log\/wtmp/||/\/var\/log\/btmp/) &&/ +-p *wa/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]**$/)' /etc/audit/rules.d/*.rules"
""
"Verify the output matches:"
"-w /var/run/utmp -p wa -k session"
" -w /var/log/wtmp -p wa -k session"
" -w /var/log/btmp -p wa -k session"
""
"###########################################################################################"
"# 2. To audit running configuration, run the following script to check loaded rules:"
"###########################################################################################"
"# auditctl -l | awk '/^ *-w/ &&(/\/var\/run\/utmp/||/\/var\/log\/wtmp/||/\/var\/log\/btmp/) &&/ +-p *wa/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)'"
""
"Verify the output matches:"
"-w /var/run/utmp -p wa -k session"
" -w /var/log/wtmp -p wa -k session"
" -w /var/log/btmp -p wa -k session "
""
)
  if [ "${#a_output_suggestion[@]}" -gt 0 ]; then
    echo ""
    echo "-- Suggestion --"
    printf '%s\n' "${a_output_suggestion[@]}"
  fi
}
BASH
    ],

    // --- 6.2.3.12 Ensure login and logout events are collected ---
    [
        'id' => '6.2.3.12', 'title' => 'Ensure login and logout events are collected', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#\!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  a_output_suggestion=()
  AUDIT_OVERALL_STATUS="MANUAL"

  echo "- Audit Result: ** MANUAL **"

  a_output_suggestion+=(
"###########################################################################################"
"# 1. To audit on-disk configuration, run the following script  to check the on disk rules:"
"###########################################################################################"
"# awk '/^ *-w/ &&(/\/var\/log\/lastlog/||/\/var\/run\/faillock/) &&/ +-p *wa/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)' /etc/audit/rules.d/*.rules "
""
"Verify the output matches:"
" -w /var/log/lastlog -p wa -k logins"
" -w /var/run/faillock -p wa -k login"
""
"###########################################################################################"
"# 2. To audit running configuration, run the following script to check loaded rules:"
"###########################################################################################"
"# auditctl -l | awk '/^ *-w/ &&(/\/var\/log\/lastlog/||/\/var\/run\/faillock/) &&/ +-p *wa/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)'"
""
"Verify the output matches:"
" -w /var/log/lastlog -p wa -k logins"
" -w /var/run/faillock -p wa -k login"
""

)
  if [ "${#a_output_suggestion[@]}" -gt 0 ]; then
    echo ""
    echo "-- Suggestion --"
    printf '%s\n' "${a_output_suggestion[@]}"
  fi
}
BASH
    ],

    // --- 6.2.3.13 Ensure file deletion events by users are collected ---
    [
        'id' => '6.2.3.13', 'title' => 'Ensure file deletion events by users are collected', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#\!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  a_output_suggestion=()
  AUDIT_OVERALL_STATUS="MANUAL"

  echo "- Audit Result: ** MANUAL **"

  a_output_suggestion+=(
"###########################################################################################"
"# 1. To audit on-disk configuration, run the following script  to check the on disk rules:"
"###########################################################################################"
"# {"
" UID_MIN=\$(awk '/^\s*UID_MIN/{print \$2}' /etc/login.defs)"
" [ -n \"\${UID_MIN}\" ] && awk \"/^ *-a *always,exit/  &&/ -F *arch=b(32|64)/  &&(/ -F *auid!=unset/||/ -F *auid!=-1/||/ -F *auid!=4294967295/) &&/ -F *auid>=\${UID_MIN}/ &&/ -S/ &&(/unlink/||/rename/||/unlinkat/||/renameat/) &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)\" /etc/audit/rules.d/*.rules || printf \"ERROR: Variable 'UID_MIN' is unset.\n\""
"}"
""
"Verify the output matches:"
" -a always,exit -F arch=b64 -S unlink,unlinkat,rename,renameat -F auid>=1000 F auid!=unset -k delete"
" -a always,exit -F arch=b32 -S unlink,unlinkat,rename,renameat -F auid>=1000 F auid!=unset -k delete"
""
"###########################################################################################"
"# 2. To audit running configuration, run the following script to check loaded rules:"
"###########################################################################################"
"# {"
" UID_MIN=\$(awk '/^\s*UID_MIN/{print \$2}' /etc/login.defs)" 
" [ -n \"\${UID_MIN}\" ] && auditctl -l | awk \"/^ *-a *always,exit/ &&/ -F *arch=b(32|64)/ &&(/ -F *auid!=unset/||/ -F *auid!=-1/||/ -F *auid!=4294967295/) &&/ -F *auid>=\${UID_MIN}/ &&/ -S/ &&(/unlink/||/rename/||/unlinkat/||/renameat/) &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)\" || printf \"ERROR: Variable 'UID_MIN' is unset.\n\"" 
"}"
""
"Verify the output matches:"
" -a always,exit -F arch=b64 -S rename,unlink,unlinkat,renameat -F auid>=1000 F auid!=-1 -F key=delete"
" -a always,exit -F arch=b32 -S unlink,rename,unlinkat,renameat -F auid>=1000 F auid!=-1 -F key=delete"
""

)
  if [ "${#a_output_suggestion[@]}" -gt 0 ]; then
    echo ""
    echo "-- Suggestion --"
    printf '%s\n' "${a_output_suggestion[@]}"
  fi
}
BASH
    ],

    // --- 6.2.3.14 Ensure events that modify the system's Mandatory Access Controls are collected ---
    [
        'id' => '6.2.3.14', 'title' => 'Ensure events that modify the system\'s Mandatory Access Controls are collected', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#\!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  a_output_suggestion=()
  AUDIT_OVERALL_STATUS="MANUAL"

  echo "- Audit Result: ** MANUAL **"

  a_output_suggestion+=(
"###########################################################################################"
"# 1. To audit on-disk configuration, run the following script  to check the on disk rules:"
"###########################################################################################"
" # awk '/^ *-w/ &&(/\/etc\/apparmor/||/\/etc\/apparmor.d/) &&/ +-p *wa/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)' /etc/audit/rules.d/*.rules"
""
"Verify the output matches:"
" -w /etc/apparmor/ -p wa -k MAC-policy"
" -w /etc/apparmor.d/ -p wa -k MAC-policy"
""
"###########################################################################################"
"# 2. To audit running configuration, run the following script to check loaded rules:"
"###########################################################################################"
"# auditctl -l | awk '/^ *-w/ &&(/\/etc\/apparmor/ ||/\/etc\/apparmor.d/) &&/ +-p *wa/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)'"
""
"Verify the output matches:"
" -w /etc/apparmor/ -p wa -k MAC-policy"
" -w /etc/apparmor.d/ -p wa -k MAC-policy"
""

)
  if [ "${#a_output_suggestion[@]}" -gt 0 ]; then
    echo ""
    echo "-- Suggestion --"
    printf '%s\n' "${a_output_suggestion[@]}"
  fi
}
BASH
    ],

    // --- 6.2.3.15 Ensure successful and unsuccessful attempts to use the chcon command are collected ---
    [
        'id' => '6.2.3.15', 'title' => 'Ensure successful and unsuccessful attempts to use the chcon command are collected', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#\!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  a_output_suggestion=()
  AUDIT_OVERALL_STATUS="MANUAL"

  echo "- Audit Result: ** MANUAL **"

  a_output_suggestion+=(
"###########################################################################################"
"# 1. To audit on-disk configuration, run the following script  to check the on disk rules:"
"###########################################################################################"
"# {"
" UID_MIN=\$(awk '/^\s*UID_MIN/{print \$2}' /etc/login.defs)"
" [ -n \"\${UID_MIN}\" ] && awk \"/^ *-a *always,exit/ &&(/ -F *auid!=unset/||/ -F *auid!=-1/||/ -F *auid!=4294967295/)&&/ -F *auid>=\${UID_MIN}/ &&/ -F *perm=x/ &&/ -F *path=\/usr\/bin\/chcon/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)\" /etc/audit/rules.d/*.rules || printf \"ERROR: Variable 'UID_MIN' is unset.\n\"" 
"}"
""
"Verify the output matches:"
" -a always,exit -F path=/usr/bin/chcon -F perm=x -F auid>=1000 -F auid!=unset -k perm_chng"
""
"###########################################################################################"
"# 2. To audit running configuration, run the following script to check loaded rules:"
"###########################################################################################"
"# {"
" UID_MIN=\$(awk '/^\s*UID_MIN/{print \$2}' /etc/login.defs)"
" [ -n \"\${UID_MIN}\" ] && auditctl -l | awk \"/^ *-a *always,exit/ &&(/ -F *auid!=unset/||/ -F *auid!=-1/||/ -F *auid!=4294967295/) &&/ -F *auid>=\${UID_MIN}/ &&/ -F *perm=x/ &&/ -F *path=\/usr\/bin\/chcon/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)\" || printf \"ERROR: Variable 'UID_MIN' is unset.\n\"" 
"}"
""
"Verify the output matches:"
" -a always,exit -S all -F path=/usr/bin/chcon -F perm=x -F auid>=1000 -F auid!=-1 -F key=perm_chng "
""

)
  if [ "${#a_output_suggestion[@]}" -gt 0 ]; then
    echo ""
    echo "-- Suggestion --"
    printf '%s\n' "${a_output_suggestion[@]}"
  fi
}
BASH
    ],

    // --- 6.2.3.16 Ensure successful and unsuccessful attempts to use the setfacl command are collected ---
    [
        'id' => '6.2.3.16', 'title' => 'Ensure successful and unsuccessful attempts to use the setfacl command are collected', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#\!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  a_output_suggestion=()
  AUDIT_OVERALL_STATUS="MANUAL"

  echo "- Audit Result: ** MANUAL **"

  a_output_suggestion+=(
"###########################################################################################"
"# 1. To audit on-disk configuration, run the following script  to check the on disk rules:"
"###########################################################################################"
"# {"
" UID_MIN=\$(awk '/^\s*UID_MIN/{print \$2}' /etc/login.defs)"
" [ -n \"\${UID_MIN}\" ] && awk \"/^ *-a *always,exit/ &&(/ -F *auid!=unset/||/ -F *auid!=-1/||/ -F *auid!=4294967295/) &&/ -F *auid>=\${UID_MIN}/ &&/ -F *perm=x/ &&/ -F *path=\/usr\/bin\/setfacl/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)\" /etc/audit/rules.d/*.rules || printf \"ERROR: Variable 'UID_MIN' is unset.\n\"" 
"}"
""
"Verify the output matches:"
"-a always,exit -F path=/usr/bin/setfacl -F perm=x -F auid>=1000 -F auid!=unset -k perm_chng "
""
"###########################################################################################"
"# 2. To audit running configuration, run the following script to check loaded rules:"
"###########################################################################################"
"# {"
" UID_MIN=\$(awk '/^\s*UID_MIN/{print \$2}' /etc/login.defs)"
" [ -n \"\${UID_MIN}\" ] && auditctl -l | awk \"/^ *-a *always,exit/ &&(/ -F *auid!=unset/||/ -F *auid!=-1/||/ -F *auid!=4294967295/) &&/ -F *auid>=\${UID_MIN}/ &&/ -F *perm=x/ &&/ -F *path=\/usr\/bin\/setfacl/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)\" || printf \"ERROR: Variable 'UID_MIN' is unset.\n\"" 
"}"
""
"Verify the output matches:"
" -a always,exit -S all -F path=/usr/bin/setfacl -F perm=x -F auid>=1000 -F auid!=-1 -F key=perm_chng"
""

)
  if [ "${#a_output_suggestion[@]}" -gt 0 ]; then
    echo ""
    echo "-- Suggestion --"
    printf '%s\n' "${a_output_suggestion[@]}"
  fi
}
BASH
    ],

    // --- 6.2.3.17 Ensure successful and unsuccessful attempts to use the chacl command are collected ---
    [
        'id' => '6.2.3.17', 'title' => 'Ensure successful and unsuccessful attempts to use the chacl command are collected', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#\!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  a_output_suggestion=()
  AUDIT_OVERALL_STATUS="MANUAL"

  echo "- Audit Result: ** MANUAL **"

  a_output_suggestion+=(
"###########################################################################################"
"# 1. To audit on-disk configuration, run the following script  to check the on disk rules:"
"###########################################################################################"
"# {"
" UID_MIN=\$(awk '/^\s*UID_MIN/{print \$2}' /etc/login.defs)"
" [ -n \"\${UID_MIN}\" ] && awk \"/^ *-a *always,exit/ &&(/ -F *auid!=unset/||/ -F *auid!=-1/||/ -F *auid!=4294967295/) &&/ -F *auid>=\${UID_MIN}/ &&/ -F *perm=x/ &&/ -F *path=\/usr\/bin\/chacl/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)\" /etc/audit/rules.d/*.rules || printf \"ERROR: Variable 'UID_MIN' is unset.\n\"" 
"}"
""
"Verify the output matches:"
"-a always,exit -F path=/usr/bin/chacl -F perm=x -F auid>=1000 -F auid!=unset -k perm_chng"
""
"###########################################################################################"
"# 2. To audit running configuration, run the following script to check loaded rules:"
"###########################################################################################"
"# {"
" UID_MIN=\$(awk '/^\s*UID_MIN/{print \$2}' /etc/login.defs)"
" [ -n \"\${UID_MIN}\" ] && auditctl -l | awk \"/^ *-a *always,exit/ &&(/ -F *auid!=unset/||/ -F *auid!=-1/||/ -F *auid!=4294967295/) &&/ -F *auid>=\${UID_MIN}/ &&/ -F *perm=x/ &&/ -F *path=\/usr\/bin\/chacl/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)\"  || printf \"ERROR: Variable 'UID_MIN' is unset.\n\"" 
"}"
""
"Verify the output matches:"
" -a always,exit -S all -F path=/usr/bin/chacl -F perm=x -F auid>=1000 -F auid!=-1 -F key=perm_chng"
""

)
  if [ "${#a_output_suggestion[@]}" -gt 0 ]; then
    echo ""
    echo "-- Suggestion --"
    printf '%s\n' "${a_output_suggestion[@]}"
  fi
}
BASH
    ],

    // --- 6.2.3.18 Ensure successful and unsuccessful attempts to use the usermod command are collected ---
    [
        'id' => '6.2.3.18', 'title' => 'Ensure successful and unsuccessful attempts to use the usermod command are collected', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#\!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  a_output_suggestion=()
  AUDIT_OVERALL_STATUS="MANUAL"

  echo "- Audit Result: ** MANUAL **"

  a_output_suggestion+=(
"###########################################################################################"
"# 1. To audit on-disk configuration, run the following script  to check the on disk rules:"
"###########################################################################################"
"# {"
" UID_MIN=\$(awk '/^\s*UID_MIN/{print \$2}' /etc/login.defs)"
" [ -n \"\${UID_MIN}\" ] && awk \"/^ *-a *always,exit/ &&(/ -F *auid!=unset/||/ -F *auid!=-1/||/ -F *auid!=4294967295/) &&/ -F *auid>=\${UID_MIN}/ &&/ -F *perm=x/ &&/ -F *path=\/usr\/sbin\/usermod/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)\" /etc/audit/rules.d/*.rules || printf \"ERROR: Variable 'UID_MIN' is unset.\n\"" 
"}"
""
"Verify the output matches:"
" -a always,exit -F path=/usr/sbin/usermod -F perm=x -F auid>=1000 -F auid!=unset -k usermod"
""
"###########################################################################################"
"# 2. To audit running configuration, run the following script to check loaded rules:"
"###########################################################################################"
"# {"
" UID_MIN=\$(awk '/^\s*UID_MIN/{print \$2}' /etc/login.defs)" 
" [ -n \"\${UID_MIN}\" ] && auditctl -l | awk \"/^ *-a *always,exit/ &&(/ -F *auid!=unset/||/ -F *auid!=-1/||/ -F *auid!=4294967295/) &&/ -F *auid>=\${UID_MIN}/ &&/ -F *perm=x/ &&/ -F *path=\/usr\/sbin\/usermod/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)\" || printf \"ERROR: Variable 'UID_MIN' is unset.\n\"" 
"}"
""
"Verify the output matches:"
" -a always,exit -S all -F path=/usr/sbin/usermod -F perm=x -F auid>=1000 -F auid!=-1 -F key=usermod"
""

)
  if [ "${#a_output_suggestion[@]}" -gt 0 ]; then
    echo ""
    echo "-- Suggestion --"
    printf '%s\n' "${a_output_suggestion[@]}"
  fi
}
BASH
    ],

    // --- 6.2.3.19 Ensure kernel module loading unloading and modification is collected ---
    [
        'id' => '6.2.3.19', 'title' => 'Ensure kernel module loading unloading and modification is collected', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#\!/usr/bin/env bash
{
  a_output_pass=()
  a_output_fail=()
  a_output_suggestion=()
  AUDIT_OVERALL_STATUS="MANUAL"

  echo "- Audit Result: ** MANUAL **"

  a_output_suggestion+=(
"###########################################################################################"
"# 1. To audit on-disk configuration, run the following script  to check the on disk rules:"
"###########################################################################################"
' #!/usr/bin/env bash'
"{"
"  awk '/^ *-a *always,exit/ &&/ -F *arch=b(32|64)/ &&(/ -F auid!=unset/||/ -F auid!=-1/||/ -F auid!=4294967295/) &&/ -S/ &&(/init_module/||/finit_module/||/delete_module/||/create_module/||/query_module/) &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)' /etc/audit/rules.d/*.rules" 
""
"  UID_MIN=\$(awk '/^\s*UID_MIN/{print \$2}' /etc/login.defs)" 
"  [ -n \"\${UID_MIN}\" ] && awk \"/^ *-a *always,exit/ &&(/ -F *auid!=unset/||/ -F *auid!=-1/||/ -F *auid!=4294967295/) &&/ -F *auid>=\${UID_MIN}/ &&/ -F *perm=x/ &&/ -F *path=\/usr\/bin\/kmod/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)\" /etc/audit/rules.d/*.rules || printf \"ERROR: Variable 'UID_MIN' is unset.\n\"" 
"}"
""
"Verify the output matches:"
" -a always,exit -F arch=b64 -S init_module,finit_module,delete_module,create_module,query_module -F auid>=1000 -F auid!=unset -k kernel_modules"
" -a always,exit -F path=/usr/bin/kmod -F perm=x -F auid>=1000 -F auid!=unset k kernel_modules"
""
"###########################################################################################"
"# 2. To audit running configuration, run the following script to check loaded rules:"
"###########################################################################################"
'#!/usr/bin/env bash'
"{"
"  auditctl -l | awk '/^ *-a *always,exit/ &&/ -F *arch=b(32|64)/ &&(/ -F auid!=unset/||/ -F auid!=-1/||/ -F auid!=4294967295/) &&/ -S/ &&(/init_module/||/finit_module/||/delete_module/||/create_module/||/query_module/) &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)'"
""
"  UID_MIN=\$(awk '/^\s*UID_MIN/{print \$2}' /etc/login.defs)"
"  [ -n \"\${UID_MIN}\" ] && auditctl -l | awk \"/^ *-a *always,exit/ &&(/ -F *auid!=unset/||/ -F *auid!=-1/||/ -F *auid!=4294967295/) &&/ -F *auid>=\${UID_MIN}/ &&/ -F *perm=x/ &&/ -F *path=\/usr\/bin\/kmod/ &&(/ key= *[!-~]* *$/||/ -k *[!-~]* *$/)\" \ || printf \"ERROR: Variable 'UID_MIN' is unset.\n\"" 
"}"
""
"Verify the output matches:"
" -a always,exit -F arch=b64 -S create_module,init_module,delete_module,query_module,finit_module -F auid>=1000 -F auid!=-1 -F key=kernel_modules"
" -a always,exit -S all -F path=/usr/bin/kmod -F perm=x -F auid>=1000 -F auid!=-1 -F key=kernel_modules"
""
"###########################################################################################"
"# 3. To audit Symlink audit, run the following script to audit if the symlinks kmod accepts are indeed pointing at it: "
"###########################################################################################"
'#!/usr/bin/env bash'
"{"
"   a_files=(\"/usr/sbin/lsmod\" \"/usr/sbin/rmmod\" \"/usr/sbin/insmod\" \"/usr/sbin/modinfo\" \"/usr/sbin/modprobe\" \"/usr/sbin/depmod\")"
"   for l_file in \"\${a_files[@]}\"; do"
"      if [ \"\$(readlink -f \"$l_file\")\" = \"\$(readlink -f /bin/kmod)\" ]; then"
'         printf "OK: \"$l_file\"\"'
"      else"
'         printf "Issue with symlink for file: \"$l_file\"\n"'
"      fi"
"   done"
"}"
""
"Verify the output states OK. If there is a symlink pointing to a different location it should be investigated"
""

)
  if [ "${#a_output_suggestion[@]}" -gt 0 ]; then
    echo ""
    echo "-- Suggestion --"
    printf '%s\n' "${a_output_suggestion[@]}"
  fi
}
BASH
    ],

    // --- 6.2.3.20 Ensure the audit configuration is immutable ---
    [
        'id' => '6.2.3.20', 'title' => 'Ensure the audit configuration is immutable', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  AUDIT_RULES_DIR="/etc/audit/rules.d"
  EXPECTED_IMMUTABILITY_RULE="-e 2"

  EFFECTIVE_AUDIT_RULES_ON_DISK=$(
    find "${AUDIT_RULES_DIR}" -type f -name '*.rules' -print0 2>/dev/null |
    xargs -0 cat 2>/dev/null |
    grep -P '^\h*-e\h+\d+\b' | tail -n 1
  )

  if [[ "$EFFECTIVE_AUDIT_RULES_ON_DISK" == "$EXPECTED_IMMUTABILITY_RULE" ]]; then
    a_output_pass+=(" - On-disk audit configuration includes '$EXPECTED_IMMUTABILITY_RULE' as the effective immutability rule (correct).")
  else
    if [[ -n "$EFFECTIVE_AUDIT_RULES_ON_DISK" ]]; then
      a_output_fail+=(" - On-disk audit configuration 'effective -e rule' is '$EFFECTIVE_AUDIT_RULES_ON_DISK', not '$EXPECTED_IMMUTABILITY_RULE'.")
    else
      a_output_fail+=(" - On-disk audit configuration does not include an effective immutability rule ('$EXPECTED_IMMUTABILITY_RULE').")
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
  fi
}
BASH
    ],

    // --- 6.2.3.21 Ensure the running and on disk configuration is the same ---
    [
        'id' => '6.2.3.21', 'title' => 'Ensure the running and on disk configuration is the same', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  AUGENRULES_CHECK_OUTPUT=$(augenrules --check 2>&1)

  if echo "$AUGENRULES_CHECK_OUTPUT" | grep -q "/usr/sbin/augenrules: No change"; then
    a_output_pass+=(" - On-disk and running audit configurations are in sync ('augenrules --check' showed 'No change').")
  else
    a_output_fail+=(" - On-disk and running audit configurations are NOT in sync. Output from 'augenrules --check':")
    while IFS= read -r line; do
      a_output_fail+=("   $line")
    done <<< "$AUGENRULES_CHECK_OUTPUT"
    a_output_fail+=("   (Run 'augenrules --load' to merge and load all rules, then re-audit).")
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

    // --- 6.2.4 Configure auditd File Access ---
    [ 'id' => '6.2.4', 'title' => 'Configure auditd File Access', 'type' => 'header'],

    // --- 6.2.4.1 Ensure audit log files mode is configured ---
    [
        'id' => '6.2.4.1', 'title' => 'Ensure audit log files mode is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  AUDITD_CONF_FILE="/etc/audit/auditd.conf"
  AUDIT_CONFIG_PATH="/etc/audit/"
  l_perm_mask="0137"
  l_maxperm="$(printf '%o' $(( 0777 & ~$l_perm_mask )))"
  if [ ! -d "$AUDIT_CONFIG_PATH" ]; then
    a_output_fail+=(" - Audit configuration directory '$AUDIT_CONFIG_PATH' does not exist. Audit log file mode cannot be verified.")
  else
    if [ ! -e "$AUDITD_CONF_FILE" ]; then
      a_output_fail+=(" - Auditd configuration file '$AUDITD_CONF_FILE' does not exist within '$AUDIT_CONFIG_PATH'. Cannot verify file modes.")
    else
      l_audit_log_directory="$(dirname "$(awk -F= '/^\s*log_file\s*/{print $2}' "$AUDITD_CONF_FILE" | xargs)")"

      if [ -z "$l_audit_log_directory" ]; then
        a_output_fail+=(" - Audit log directory is not set in '$AUDITD_CONF_FILE'. Cannot verify file modes.")
      elif [ ! -d "$l_audit_log_directory" ]; then
        a_output_fail+=(" - Configured audit log directory '$l_audit_log_directory' does not exist. Cannot verify file modes.")
      else
        UNAUTHORIZED_MODE_FILES=$(find "$l_audit_log_directory" -maxdepth 1 -type f -perm /"$l_perm_mask" -print 2>/dev/null)

        if [[ -z "$UNAUTHORIZED_MODE_FILES" ]]; then
          a_output_pass+=(" - All audit log files in '$l_audit_log_directory' are mode '$l_maxperm' or more restrictive (correct).")
        else
          a_output_fail+=(" - The following audit log files in '$l_audit_log_directory' have modes less restrictive than '$l_maxperm':")
          while IFS= read -r l_file; do
            l_file_mode="$(stat -Lc '%#a' "$l_file")"
            a_output_fail+=("   - File: '$l_file' is mode: '$l_file_mode' (should be '$l_maxperm' or more restrictive).")
          done <<< "$UNAUTHORIZED_MODE_FILES"
        fi
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
    if [ ! -d "$AUDIT_CONFIG_PATH" ]; then
        echo ""
        echo "  - Suggestion: Ensure 'auditd' package is installed which creates the '$AUDIT_CONFIG_PATH' directory."
        echo "    # apt install auditd"
    fi
  fi
}
BASH
    ],

    // --- 6.2.4.2 Ensure audit log files owner is configured ---
    [
        'id' => '6.2.4.2', 'title' => 'Ensure audit log files owner is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  AUDITD_CONF_FILE="/etc/audit/auditd.conf"
  AUDIT_CONFIG_PATH="/etc/audit/"
  if [ ! -d "$AUDIT_CONFIG_PATH" ]; then
    a_output_fail+=(" - Audit configuration directory '$AUDIT_CONFIG_PATH' does not exist. Audit log file owner cannot be verified.")
  else
    if [ ! -e "$AUDITD_CONF_FILE" ]; then
      a_output_fail+=(" - Auditd configuration file '$AUDITD_CONF_FILE' does not exist within '$AUDIT_CONFIG_PATH'. Cannot verify file owner.")
    else
      l_audit_log_directory="$(dirname "$(awk -F= '/^\s*log_file\s*/{print $2}' "$AUDITD_CONF_FILE" | xargs)")"

      if [ -z "$l_audit_log_directory" ]; then
        a_output_fail+=(" - Audit log directory is not set in '$AUDITD_CONF_FILE'. Cannot verify file owner.")
      elif [ ! -d "$l_audit_log_directory" ]; then
        a_output_fail+=(" - Configured audit log directory '$l_audit_log_directory' does not exist. Cannot verify file owner.")
      else
        UNAUTHORIZED_OWNER_FILES=$(find "$l_audit_log_directory" -maxdepth 1 -type f ! -user root -print 2>/dev/null)

        if [[ -z "$UNAUTHORIZED_OWNER_FILES" ]]; then
          a_output_pass+=(" - All audit log files in '$l_audit_log_directory' are owned by the 'root' user (correct).")
        else
          a_output_fail+=(" - The following audit log files in '$l_audit_log_directory' are NOT owned by the 'root' user:")
          while IFS= read -r l_file; do
            l_file_owner="$(stat -Lc '%U' "$l_file")"
            a_output_fail+=("   - File: '$l_file' is owned by user: '$l_file_owner' (should be 'root').")
          done <<< "$UNAUTHORIZED_OWNER_FILES"
        fi
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
    if [ ! -d "$AUDIT_CONFIG_PATH" ]; then
        echo ""
        echo "  - Suggestion: Ensure 'auditd' package is installed which creates the '$AUDIT_CONFIG_PATH' directory."
        echo "    # apt install auditd"
    fi
  fi
}
BASH
    ],

    // --- 6.2.4.3 Ensure audit log files group owner is configured ---
    [
        'id' => '6.2.4.3', 'title' => 'Ensure audit log files group owner is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  AUDITD_CONF_FILE="/etc/audit/auditd.conf"
  AUDIT_CONFIG_PATH="/etc/audit/"

  if [ ! -d "$AUDIT_CONFIG_PATH" ]; then
    a_output_fail+=(" - Audit configuration directory '$AUDIT_CONFIG_PATH' does not exist. Audit log file owner cannot be verified.")
  else
    if [ ! -e "$AUDITD_CONF_FILE" ]; then
      a_output_fail+=(" - Auditd configuration file '$AUDITD_CONF_FILE' does not exist within '$AUDIT_CONFIG_PATH'. Cannot verify file owner.")
    else
      l_audit_log_directory="$(dirname "$(awk -F= '/^\s*log_file\s*/{print $2}' "$AUDITD_CONF_FILE" | xargs)")"

      if [ -z "$l_audit_log_directory" ]; then
        a_output_fail+=(" - Audit log directory is not set in '$AUDITD_CONF_FILE'. Cannot verify file owner.")
      elif [ ! -d "$l_audit_log_directory" ]; then
        a_output_fail+=(" - Configured audit log directory '$l_audit_log_directory' does not exist. Cannot verify file owner.")
      else
        UNAUTHORIZED_OWNER_FILES=$(find "$l_audit_log_directory" -maxdepth 1 -type f ! -user root -print 2>/dev/null)

        if [[ -z "$UNAUTHORIZED_OWNER_FILES" ]]; then
          a_output_pass+=(" - All audit log files in '$l_audit_log_directory' are owned by the 'root' user (correct).")
        else
          a_output_fail+=(" - The following audit log files in '$l_audit_log_directory' are NOT owned by the 'root' user:")
          while IFS= read -r l_file; do
            l_file_owner="$(stat -Lc '%U' "$l_file")"
            a_output_fail+=("   - File: '$l_file' is owned by user: '$l_file_owner' (should be 'root').")
          done <<< "$UNAUTHORIZED_OWNER_FILES"
        fi
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
    if [ ! -d "$AUDIT_CONFIG_PATH" ]; then
        echo ""
        echo "  - Suggestion: Ensure 'auditd' package is installed which creates the '$AUDIT_CONFIG_PATH' directory."
        echo "    # apt install auditd"
    fi
  fi
}
BASH
    ],

    // --- 6.2.4.4 Ensure the audit log file directory mode is configured ---
    [
        'id' => '6.2.4.4', 'title' => 'Ensure the audit log file directory mode is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  AUDITD_CONF_FILE="/etc/audit/auditd.conf"
  AUDIT_CONFIG_PATH="/etc/audit/"
  l_perm_mask="0027"
  l_maxperm="$(printf '%o' $(( 0777 & ~$l_perm_mask )))"
  
  if [ ! -d "$AUDIT_CONFIG_PATH" ]; then
    a_output_fail+=(" - Audit configuration directory '$AUDIT_CONFIG_PATH' does not exist. Audit log file directory mode cannot be verified.")
  else
    if [ ! -e "$AUDITD_CONF_FILE" ]; then
      a_output_fail+=(" - Auditd configuration file '$AUDITD_CONF_FILE' does not exist within '$AUDIT_CONFIG_PATH'. Cannot verify directory mode.")
    else
      l_audit_log_directory="$(dirname "$(awk -F= '/^\s*log_file\s*/{print $2}' "$AUDITD_CONF_FILE" | xargs)")"

      if [ -z "$l_audit_log_directory" ]; then
        a_output_fail+=(" - Audit log directory is not set in '$AUDITD_CONF_FILE'. Cannot verify directory mode.")
      elif [ ! -d "$l_audit_log_directory" ]; then
        a_output_fail+=(" - Configured audit log directory '$l_audit_log_directory' does not exist. Cannot verify directory mode.")
      else
        l_directory_mode="$(stat -Lc '%#a' "$l_audit_log_directory")"

        if [ $(( l_directory_mode & l_perm_mask )) -gt 0 ]; then
          a_output_fail+=(" - Directory: '$l_audit_log_directory' is mode: '$l_directory_mode' (incorrect). Should be mode: '$l_maxperm' or more restrictive.")
        else
          a_output_pass+=(" - Directory: '$l_audit_log_directory' is mode: '$l_directory_mode' (correct, 0750 or more restrictive).")
        fi
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
    if [ ! -d "$AUDIT_CONFIG_PATH" ]; then
        echo ""
        echo "  - Suggestion: Ensure 'auditd' package is installed which creates the '$AUDIT_CONFIG_PATH' directory."
        echo "    # apt install auditd"
    fi
  fi
}
BASH
    ],

    // --- 6.2.4.5 Ensure audit configuration files mode is configured ---
    [
        'id' => '6.2.4.5', 'title' => 'Ensure audit configuration files mode is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  AUDIT_CONFIG_PATH="/etc/audit/"
  l_perm_mask="0137"
  l_maxperm="$(printf '%o' $(( 0777 & ~$l_perm_mask )))"
  if [ ! -d "$AUDIT_CONFIG_PATH" ]; then
    a_output_fail+=(" - Audit configuration directory '$AUDIT_CONFIG_PATH' does not exist. Audit configuration file modes cannot be verified.")
  else
    UNAUTHORIZED_MODE_FILES=$(find "$AUDIT_CONFIG_PATH" -type f \( -name '*.conf' -o -name '*.rules' \) -perm /"$l_perm_mask" -print 2>/dev/null)

    if [[ -z "$UNAUTHORIZED_MODE_FILES" ]]; then
      a_output_pass+=(" - All audit configuration files in '$AUDIT_CONFIG_PATH' are mode '$l_maxperm' or more restrictive (correct).")
    else
      a_output_fail+=(" - The following audit configuration files in '$AUDIT_CONFIG_PATH' have modes less restrictive than '$l_maxperm':")
      while IFS= read -r l_file; do
        l_file_mode="$(stat -Lc '%#a' "$l_file")"
        a_output_fail+=("   - File: '$l_file' is mode: '$l_file_mode' (should be '$l_maxperm' or more restrictive).")
      done <<< "$UNAUTHORIZED_MODE_FILES"
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
    if [ ! -d "$AUDIT_CONFIG_PATH" ]; then
        echo ""
        echo "  - Suggestion: Ensure 'auditd' package is installed which creates the '$AUDIT_CONFIG_PATH' directory."
        echo "    # apt install auditd"
    fi
  fi
}
BASH
    ],

    // --- 6.2.4.6 Ensure audit configuration files owner is configured ---
    [
        'id' => '6.2.4.6', 'title' => 'Ensure audit configuration files owner is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  AUDIT_CONFIG_PATH="/etc/audit/"

  if [ ! -d "$AUDIT_CONFIG_PATH" ]; then
    a_output_fail+=(" - Audit configuration directory '$AUDIT_CONFIG_PATH' does not exist. Auditd configuration cannot be verified.")
  else

    UNAUTHORIZED_OWNER_FILES=$(find "$AUDIT_CONFIG_PATH" -type f \( -name '*.conf' -o -name '*.rules' \) ! -user root 2>/dev/null)

    if [[ -z "$UNAUTHORIZED_OWNER_FILES" ]]; then
      a_output_pass+=(" - All audit configuration files in '$AUDIT_CONFIG_PATH' are owned by the 'root' user (correct).")
    else
      a_output_fail+=(" - The following audit configuration files are NOT owned by the 'root' user:")
      while IFS= read -r line; do
        a_output_fail+=("   - $line")
      done <<< "$UNAUTHORIZED_OWNER_FILES"
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
    if [ ! -d "$AUDIT_CONFIG_PATH" ]; then
        echo ""
        echo "  - Suggestion: Ensure 'auditd' package is installed which creates the '/etc/audit/' directory."
        echo "    # apt install auditd"
    fi
  fi
}
BASH
    ],

    // --- 6.2.4.7 Ensure audit configuration files group owner is configured ---
    [
        'id' => '6.2.4.7', 'title' => 'Ensure audit configuration files group owner is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  AUDIT_CONFIG_PATH="/etc/audit/"

  if [ ! -d "$AUDIT_CONFIG_PATH" ]; then
    a_output_fail+=(" - Audit configuration directory '$AUDIT_CONFIG_PATH' does not exist. Auditd configuration cannot be verified.")
  else
    UNAUTHORIZED_GROUP_FILES=$(find "$AUDIT_CONFIG_PATH" -type f \( -name '*.conf' -o -name '*.rules' \) ! -group root 2>/dev/null)

    if [[ -z "$UNAUTHORIZED_GROUP_FILES" ]]; then
      a_output_pass+=(" - All audit configuration files in '$AUDIT_CONFIG_PATH' are group-owned by the 'root' group (correct).")
    else
      a_output_fail+=(" - The following audit configuration files are NOT group-owned by the 'root' group:")
      while IFS= read -r line; do
        a_output_fail+=("   - $line")
      done <<< "$UNAUTHORIZED_GROUP_FILES"
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
    if [ ! -d "$AUDIT_CONFIG_PATH" ]; then
        echo ""
        echo "  - Suggestion: Ensure 'auditd' package is installed which creates the '/etc/audit/' directory."
        echo "    # apt install auditd"
    fi
  fi
}
BASH
    ],

    // --- 6.2.4.8 Ensure audit tools mode is configured ---
    [
        'id' => '6.2.4.8', 'title' => 'Ensure audit tools mode is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  l_perm_mask="0022" 
  l_maxperm=$(printf '%o' $(( 0777 & ~$l_perm_mask ))) 
  a_audit_tools=(
    "/sbin/auditctl"
    "/sbin/aureport"
    "/sbin/ausearch"
    "/sbin/autrace"
    "/sbin/auditd"
    "/sbin/augenrules"
  )

  if [ ! -d "/etc/audit/" ]; then
    a_output_fail+=(" - Audit configuration directory '/etc/audit/' does not exist. Audit tools audit cannot be performed.")
  else
    for l_audit_tool in "${a_audit_tools[@]}"; do
      if [ ! -f "$l_audit_tool" ]; then
        a_output_fail+=(" - Audit tool '$l_audit_tool' does not exist. Cannot verify its mode.")
        continue
      fi

      l_mode=$(stat -Lc '%#a' "$l_audit_tool")

      if [ $(( l_mode & l_perm_mask )) -gt 0 ]; then
        a_output_fail+=(" - Audit tool '$l_audit_tool' is mode: '$l_mode' (incorrect). Should be mode: '$l_maxperm' or more restrictive.")
      else
        a_output_pass+=(" - Audit tool '$l_audit_tool' is correctly configured to mode: '$l_mode' (0755 or more restrictive).")
      fi
    done
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
    if [ ! -d "/etc/audit/" ]; then
        echo ""
        echo "  - Suggestion: Ensure 'auditd' package is installed which provides audit tools and creates the '/etc/audit/' directory."
        echo "    # apt install auditd"
    fi
  fi
}
BASH
    ],

    // --- 6.2.4.9 Ensure audit tools owner is configured ---
    [
        'id' => '6.2.4.9', 'title' => 'Ensure audit tools owner is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  a_audit_tools=(
    "/sbin/auditctl"
    "/sbin/aureport"
    "/sbin/ausearch"
    "/sbin/autrace"
    "/sbin/auditd"
    "/sbin/augenrules"
  )

  if [ ! -d "/etc/audit/" ]; then
    a_output_fail+=(" - Audit configuration directory '/etc/audit/' does not exist. Audit tools audit cannot be performed.")
  else
    UNAUTHORIZED_OWNER_TOOLS=""
    for l_audit_tool in "${a_audit_tools[@]}"; do
      if [ ! -f "$l_audit_tool" ]; then
        a_output_fail+=(" - Audit tool '$l_audit_tool' does not exist. Cannot verify its owner.")
        continue
      fi

      TOOL_OWNER_CHECK=$(stat -Lc "%n %U" "$l_audit_tool" 2>/dev/null | awk '$2 != "root" {print}')
      
      if [[ -n "$TOOL_OWNER_CHECK" ]]; then
        UNAUTHORIZED_OWNER_TOOLS+="$TOOL_OWNER_CHECK\n"
      fi
    done

    if [[ -z "$UNAUTHORIZED_OWNER_TOOLS" ]]; then
      a_output_pass+=(" - All audit tools are owned by the 'root' user (correct).")
    else
      a_output_fail+=(" - The following audit tools are NOT owned by the 'root' user:")
      while IFS= read -r line; do
        a_output_fail+=("   - $line")
      done <<< "$UNAUTHORIZED_OWNER_TOOLS"
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
    if [ ! -d "/etc/audit/" ]; then
        echo ""
        echo "  - Suggestion: Ensure 'auditd' package is installed which provides audit tools and creates the '/etc/audit/' directory."
        echo "    # apt install auditd"
    fi
  fi
}
BASH
    ],

    // --- 6.2.4.10 Ensure audit tools group owner is configured ---
    [
        'id' => '6.2.4.10', 'title' => 'Ensure audit tools group owner is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  a_audit_tools=(
    "/sbin/auditctl"
    "/sbin/aureport"
    "/sbin/ausearch"
    "/sbin/autrace"
    "/sbin/auditd"
    "/sbin/augenrules"
  )

  if [ ! -d "/etc/audit/" ]; then
    a_output_fail+=(" - Audit configuration directory '/etc/audit/' does not exist. Audit tools audit cannot be performed.")
  else
    UNAUTHORIZED_GROUP_TOOLS=""
    for l_audit_tool in "${a_audit_tools[@]}"; do
      if [ ! -f "$l_audit_tool" ]; then
        a_output_fail+=(" - Audit tool '$l_audit_tool' does not exist. Cannot verify its group owner.")
        continue
      fi

      TOOL_GROUP_CHECK=$(stat -Lc "%n %G" "$l_audit_tool" 2>/dev/null | awk '$2 != "root" {print}')
      
      if [[ -n "$TOOL_GROUP_CHECK" ]]; then
        UNAUTHORIZED_GROUP_TOOLS+="$TOOL_GROUP_CHECK\n"
      fi
    done

    if [[ -z "$UNAUTHORIZED_GROUP_TOOLS" ]]; then
      a_output_pass+=(" - All audit tools are group-owned by the 'root' group (correct).")
    else
      a_output_fail+=(" - The following audit tools are NOT group-owned by the 'root' group:")
      while IFS= read -r line; do
        a_output_fail+=("   - $line")
      done <<< "$UNAUTHORIZED_GROUP_TOOLS"
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
    if [ ! -d "/etc/audit/" ]; then
        echo ""
        echo "  - Suggestion: Ensure 'auditd' package is installed which provides audit tools and creates the '/etc/audit/' directory."
        echo "    # apt install auditd"
    fi
  fi
}
BASH
    ],

    // --- 6.3 Configure Integrity Checking ---
    [ 'id' => '6.3', 'title' => 'Configure Integrity Checking', 'type' => 'header' ],

    // --- 6.3.1 Ensure AIDE is installed ---
    [
        'id' => '6.3.2', 'title' => 'Ensure AIDE is installed', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" # Default to FAIL

  # Audit 1: Verify aide is installed.
  if dpkg-query -s aide &>/dev/null; then
    a_output_pass+=(" - 'aide' package is installed (correct).")
  else
    a_output_fail+=(" - 'aide' package is not installed.")
  fi

  # Audit 2: Verify aide-common is installed.
  if dpkg-query -s aide-common &>/dev/null; then
    a_output_pass+=(" - 'aide-common' package is installed (correct).")
  else
    a_output_fail+=(" - 'aide-common' package is not installed.")
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

    // --- 6.3.2 Ensure filesystem integrity is regularly checked ---
    [
        'id' => '6.3.2', 'title' => 'Ensure filesystem integrity is regularly checked', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  DAILYAIDECHECK_STATUSES=$(systemctl list-unit-files | awk '$1~/^dailyaidecheck\.(timer|service)$/{print $1 "\t" $2}')

  TIMER_ENABLED="false"
  SERVICE_STATUS_OK="false"

  while IFS=$'\t' read -r unit_name status; do
    if [[ "$unit_name" == "dailyaidecheck.timer" ]]; then
      if [[ "$status" == "enabled" ]]; then
        TIMER_ENABLED="true"
      fi
    elif [[ "$unit_name" == "dailyaidecheck.service" ]]; then
      if [[ "$status" == "static" || "$status" == "enabled" ]]; then
        SERVICE_STATUS_OK="true"
      fi
    fi
  done <<< "$DAILYAIDECHECK_STATUSES"

  if [[ "$TIMER_ENABLED" == "true" && "$SERVICE_STATUS_OK" == "true" ]]; then
    a_output_pass+=(" - 'dailyaidecheck.timer' is enabled and 'dailyaidecheck.service' is static/enabled (correct).")
  else
    if [[ "$TIMER_ENABLED" == "false" ]]; then
      a_output_fail+=(" - 'dailyaidecheck.timer' is not enabled.")
    fi
    if [[ "$SERVICE_STATUS_OK" == "false" ]]; then
      a_output_fail+=(" - 'dailyaidecheck.service' is neither 'static' nor 'enabled'.")
    fi
  fi

  DAILYAIDECHECK_TIMER_ACTIVE=$(systemctl is-active dailyaidecheck.timer 2>/dev/null)

  if [[ "$DAILYAIDECHECK_TIMER_ACTIVE" == "active" ]]; then
    a_output_pass+=(" - 'dailyaidecheck.timer' is active (correct).")
  else
    a_output_fail+=(" - 'dailyaidecheck.timer' is not active. Current status: '$DAILYAIDECHECK_TIMER_ACTIVE'.")
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

    // --- 6.3.3 Ensure cryptographic mechanisms are used to protect the integrity of audit tools ---
    [
        'id' => '6.3.3', 'title' => 'Ensure cryptographic mechanisms are used to protect the integrity of audit tools', 'profile' => 'Level 2 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
   a_output=() a_output2=() l_tool_dir="$(readlink -f /sbin)"
   a_items=("p" "i" "n" "u" "g" "s" "b" "acl" "xattrs" "sha512")
   l_aide_cmd="$(whereis aide | awk '{print $2}')"
   a_audit_files=("auditctl" "auditd" "ausearch" "aureport" "autrace" "augenrules")
   if [ -f "$l_aide_cmd" ] && command -v "$l_aide_cmd" &>/dev/null; then
      a_aide_conf_files=("$(find -L /etc -type f -name 'aide.conf')")
      f_file_par_chk()
      {
         a_out2=()
         for l_item in "${a_items[@]}"; do
            ! grep -Psiq -- '(\h+|\+)'"$l_item"'(\h+|\+)' <<< "$l_out" && \
            a_out2+=("  - Missing the \"$l_item\" option")
         done
         if [ "${#a_out2[@]}" -gt "0" ]; then
            a_output2+=(" - Audit tool file: \"$l_file\"" "${a_out2[@]}")
         else
            a_output+=(" - Audit tool file: \"$l_file\" includes:" " \"${a_items[*]}\"")
         fi
      }
      for l_file in "${a_audit_files[@]}"; do
         if [ -f "$l_tool_dir/$l_file" ]; then
            l_out="$("$l_aide_cmd" --config "${a_aide_conf_files[@]}" -p f:"$l_tool_dir/$l_file")"
            f_file_par_chk
         else
            a_output+=("  - Audit tool file \"$l_file\" doesn't exist")
         fi
      done
   else
      a_output2+=("  - The command \"aide\" was not found"  "    Please install AIDE")
   fi
   if [ "${#a_output2[@]}" -le 0 ]; then
      printf '%s\n' "" "- Audit Result:" "  ** PASS **" "${a_output[@]}" ""
   else
      printf '%s\n' "" "- Audit Result:" "  ** FAIL **" " - Reason(s) for audit failure:" "${a_output2[@]}"
      [ "${#a_output[@]}" -gt 0 ] && printf '%s\n' "" "- Correctly set:" "${a_output[@]}" ""
   fi
}
BASH
    ],
];
