<?PHP
  //preg_replace("/[\n\n]/", "</p>\n\n<p>", $input);
  //return preg_replace("/[\n]/", "<br />\n", $input);
  //

  function translate_lessgreater($input)
  {
    return preg_replace("/[>]/", "&gt;", preg_replace("/[<]/", "&lt;", $input));
  }

  function translate_slash_t_to_tab($input, $tabs="")
  {
    return preg_replace("/[\t]/", "&nbsp;", $input);
  }

  function translate_slash_n_to_br($input, $tabs="")
  {
    return preg_replace("/[\n]/", "<br />\n", $input);
  }

  function translate_html($input, $tabs="")
  {
    //return translate_slash_n_to_br($input, $tabs);

    $output="";

    /*foreach ($keywords as $keyword)
    {
        $search[]  = "/(\\b$keyword\\b)/" . $cm;
        $replace[] = '<span class="keyword">\\0</span>';
    }

    $search[]  = "/(\\bclass\s)/";
    $replace[] = '\\0';

    return preg_replace($search, $replace, $text);*/

    //$inputa = preg_split ("/[\s,]+/", $input);
     //$inputa = preg_split ('[\n]', $input);
    $inputa = preg_split ("/[\n]/", $input);

    if ($tabs) {
      for ($i = 0; $i < count($inputa); $i++) {
        $inputa[$i] = $tabs . $inputa[$i] . "\n";
      }
    }

    for ($i = 0; $i < count($inputa); $i++) {
      $row = $inputa[$i];
      $line = "";
      for ($b = 0; $b < strlen($row); $b++) {
        if($row[$b]!='\r' && $row[$b]!='\n') {
          $line .= $row[$b] . "<br />\n";
        }
      }

      //check for 0D

      $output .= $line . "\n";

      /*if (substr($row,0,1) == "#") {
        $rowa = explode("\t",$row);
        if (count($rowa) >= 2) {
          $keyval = trim(substr($rowa[0],1));
          $valval = trim($rowa[count($rowa)-1]);
          if ($valval == "true") $valval = true;
          else if ($valval == "false") $valval = false;

          $andromedaPrefs[$keyval] = $valval;
        }
      }*/
    }

    //TODO: Fix this so that it actually does something
    return $input;
  }








  function keyword_replace($keywords, $text, $ncs = false)
  {
    $cm = ($ncs) ? "i" : "";
    foreach ($keywords as $keyword) {
      $search[]  = "/(\\b$keyword\\b)/" . $cm;
      $replace[] = '<span class="keyword">\\0</span>';
    }

    $search[]  = "/(\\bclass\s)/";
    $replace[] = '<span class="keyword">\\0</span>';

    return preg_replace($search, $replace, $text);
  }
  
  
  function preproc_replace($preproc, $text)
  {
    foreach ($preproc as $proc) {
      $search[] = "/(\\s*#\s*$proc\\b)/";
      $replace[] = '<span class="preproc">\\0</span>';
    }

    return preg_replace($search, $replace, $text);
  }
  
  //TODO: Replace "string" with correct colouring
  //do not match \"string
  //do match 
  // "string etc.
  function string_replace($string, $text)
  {
    foreach($string as $s) {
      $search[] = "/(\\s*#\s*$string\\b)/";
      $replace[] = '<span class="string">\\0</span>';
    }

    return preg_replace($search, $replace, $text);
  }

  //TODO: Replace x=1 with correct colouring
  // (0
  //  0
  // =0
  // ,0
  function number_replace($value, $text)
  {
    foreach($value as $v) {
      $search[] = "/(\\s*#\s*$value\\b)/";
      $replace[] = '<span class="number">\\0</span>';
    }

    return $text;//preg_replace($search, $replace, $text);
  }
  
  
  function syntax_highlight_helper($text, $language)
  {
    $preproc = array();
      
    $preproc["c++"] = array(
      "if",    "ifdef",   "ifndef", "elif",  "else",
      "endif", "include", "define", "undef", "line",
      "error", "pragma");
  
    $keywords = array(
      "c++" => array(
      "asm",          "auto",      "bool",             "break",            "case",
      "catch",        "char",      "const",            "const_cast",       "continue",
      "default",      "delete",    "do",               "double",           "dynamic_cast",
      "else",         "enum",      "explicit",         "export",           "extern",
      "false",        "float",     "for",              "friend",           "goto",
      "if",           "inline",    "int",              "long",             "mutable",
      "namespace",    "new",       "operator",         "private",          "protected",
      "public",       "register",  "reinterpret_cast", "return",           "short",
      "signed",       "sizeof",    "static",           "static_cast",      "struct",
      "switch",       "template",  "this",             "throw",            "true",
      "try",          "typedef",   "typeid",           "typename",         "union",
      "unsigned",     "using",     "virtual",          "void",             "volatile",
      "wchar_t",      "while"),
  
      "php" => array(
      "and",          "or",           "xor",      "__FILE__",     "__LINE__",
      "array",        "as",           "break",    "case",         "cfunction",
      /*class*/       "const",        "continue", "declare",      "default",
      "die",          "do",           "echo",     "else",         "elseif",
      "empty",        "enddeclare",   "endfor",   "endforeach",   "endif",
      "endswitch",    "endwhile",     "eval",     "exit",         "extends",
      "for",          "foreach",      "function", "global",       "if",
      "include",      "include_once", "isset",    "list",         "new",
      "old_function", "print",        "require",  "require_once", "return",
      "static",       "switch",       "unset",    "use",          "var",
      "while",        "__FUNCTION__", "__CLASS__"),

      "java" => array(
      "abstract",     "boolean",      "break",    "byte",         "case",
      "catch",        "char",         /*class*/   "const",        "continue",
      "default",      "do",           "double",   "else",         "extends",
      "final",        "finally",      "float",    "for",          "goto",
      "if",           "implements",   "import",   "instanceof",   "int",
      "interface",    "long",         "native",   "new",          "package",
      "private",      "protected",    "public",   "return",       "short",
      "static",       "strictfp",     "super",    "switch",       "synchronized",
      "this",         "throw",        "throws",   "transient",    "try",
      "void",         "volatile",     "while"),

      "sql" => array(
      "abort", "abs", "absolute", "access",
      "action", "ada", "add", "admin",
      "after", "aggregate", "alias", "all",
      "allocate", "alter", "analyse", "analyze",
      "and", "any", "are", "array",
      "as", "asc", "asensitive", "assertion",
      "assignment", "asymmetric", "at", "atomic",
      "authorization", "avg", "backward", "before",
      "begin", "between", "bigint", "binary",
      "bit", "bitvar", "bit_length", "blob",
      "boolean", "both", "breadth", "by",
      "c", "cache", "call", "called",
      "cardinality", "cascade", "cascaded", "case",
      "cast", "catalog", "catalog_name", "chain",
      "char", "character", "characteristics", "character_length",
      "character_set_catalog", "character_set_name", "character_set_schema", "char_length",
      "check", "checked", "checkpoint", /* "class", */
      "class_origin", "clob", "close", "cluster",
      "coalesce", "cobol", "collate", "collation",
      "collation_catalog", "collation_name", "collation_schema", "column",
      "column_name", "command_function", "command_function_code", "comment",
      "commit", "committed", "completion", "condition_number",
      "connect", "connection", "connection_name", "constraint",
      "constraints", "constraint_catalog", "constraint_name", "constraint_schema",
      "constructor", "contains", "continue", "conversion",
      "convert", "copy", "corresponding", "count",
      "create", "createdb", "createuser", "cross",
      "cube", "current", "current_date", "current_path",
      "current_role", "current_time", "current_timestamp", "current_user",
      "cursor", "cursor_name", "cycle", "data",
      "database", "date", "datetime_interval_code", "datetime_interval_precision",
      "day", "deallocate", "dec", "decimal",
      "declare", "default", "defaults", "deferrable",
      "deferred", "defined", "definer", "delete",
      "delimiter", "delimiters", "depth", "deref",
      "desc", "describe", "descriptor", "destroy",
      "destructor", "deterministic", "diagnostics", "dictionary",
      "disconnect", "dispatch", "distinct", "do",
      "domain", "double", "drop", "dynamic",
      "dynamic_function", "dynamic_function_code", "each", "else",
      "encoding", "encrypted", "end", "end-exec",
      "equals", "escape", "every", "except",
      "exception", "excluding", "exclusive", "exec",
      "execute", "existing", "exists", "explain",
      "external", "extract", "false", "fetch",
      "final", "first", "float", "for",
      "force", "foreign", "fortran", "forward",
      "found", "free", "freeze", "from",
      "full", "function", "g", "general",
      "generated", "get", "global", "go",
      "goto", "grant", "granted", "group",
      "grouping", "handler", "having", "hierarchy",
      "hold", "host", "hour", "identity",
      "ignore", "ilike", "immediate", "immutable",
      "implementation", "implicit", "in", "including",
      "increment", "index", "indicator", "infix",
      "inherits", "initialize", "initially", "inner",
      "inout", "input", "insensitive", "insert",
      "instance", "instantiable", "instead", "int",
      "integer", "intersect", "interval", "into",
      "invoker", "is", "isnull", "isolation",
      "iterate", "join", "k", "key",
      "key_member", "key_type", "lancompiler", "language",
      "large", "last", "lateral", "leading",
      "left", "length", "less", "level",
      "like", "limit", "listen", "load",
      "local", "localtime", "localtimestamp", "location",
      "locator", "lock", "lower", "m",
      "map", "match", "max", "maxvalue",
      "message_length", "message_octet_length", "message_text", "method",
      "min", "minute", "minvalue", "mod",
      "mode", "modifies", "modify", "module",
      "month", "more", "move", "mumps",
      "name", "names", "national", "natural",
      "nchar", "nclob", "new", "next",
      "no", "nocreatedb", "nocreateuser", "none",
      "not", "nothing", "notify", "notnull",
      "null", "nullable", "nullif", "number",
      "numeric", "object", "octet_length", "of",
      "off", "offset", "oids", "old",
      "on", "only", "open", "operation",
      "operator", "option", "options", "or",
      "order", "ordinality", "out", "outer",
      "output", "overlaps", "overlay", "overriding",
      "owner", "pad", "parameter", "parameters",
      "parameter_mode", "parameter_name", "parameter_ordinal_position", "parameter_specific_catalog",
      "parameter_specific_name", "parameter_specific_schema", "partial", "pascal",
      "password", "path", "pendant", "placing",
      "pli", "position", "postfix", "precision",
      "prefix", "preorder", "prepare", "preserve",
      "primary", "prior", "privileges", "procedural",
      "procedure", "public", "read", "reads",
      "real", "recheck", "recursive", "ref",
      "references", "referencing", "reindex", "relative",
      "rename", "repeatable", "replace", "reset",
      "restart", "restrict", "result", "return",
      "returned_length", "returned_octet_length", "returned_sqlstate", "returns",
      "revoke", "right", "role", "rollback",
      "rollup", "routine", "routine_catalog", "routine_name",
      "routine_schema", "row", "rows", "row_count",
      "rule", "savepoint", "scale", "schema",
      "schema_name", "scope", "scroll", "search",
      "second", "section", "security", "select",
      "self", "sensitive", "sequence", "serializable",
      "server_name", "session", "session_user", "set",
      "setof", "sets", "share", "show",
      "similar", "simple", "size", "smallint",
      "some", "source", "space", "specific",
      "specifictype", "specific_name", "sql", "sqlcode",
      "sqlerror", "sqlexception", "sqlstate", "sqlwarning",
      "stable", "start", "state", "statement",
      "static", "statistics", "stdin", "stdout",
      "storage", "strict", "structure", "style",
      "subclass_origin", "sublist", "substring", "sum",
      "symmetric", "sysid", "system", "system_user",
      "table", "table_name", "temp", "template",
      "temporary", "terminate", "text", "than", "then",
      "time", "timestamp", "timezone_hour", "timezone_minute",
      "to", "toast", "trailing", "transaction",
      "transactions_committed", "transactions_rolled_back", "transaction_active", "transform",
      "transforms", "translate", "translation", "treat",
      "trigger", "trigger_catalog", "trigger_name", "trigger_schema",
      "trim", "true", "truncate", "trusted",
      "type", "uncommitted", "under", "unencrypted",
      "union", "unique", "unknown", "unlisten",
      "unnamed", "unnest", "until", "update",
      "upper", "usage", "user", "user_defined_type_catalog",
      "user_defined_type_name", "user_defined_type_schema", "using", "vacuum",
      "valid", "validator", "value", "values",
      "varchar", "variable", "varying", "verbose",
      "version", "view", "volatile", "when",
      "whenever", "where", "with", "without",
      "work", "write", "year", "zone")
    );
  
    $case_insensitive = array(
      "sql"    => true);

    $ncs = false;
    if (array_key_exists($language, $case_insensitive))
      $ncs = true;
  
    $text = (array_key_exists($language, $preproc))?
      preproc_replace($preproc[$language], $text) :
      $text;

    $text = (array_key_exists($language, $keywords))?
      keyword_replace($keywords[$language], $text, $ncs) :
      $text;
  
    return $text;
  }
  
  
  function rtrim1($span, $lang, $ch)
  {
    return syntax_highlight_helper(substr($span, 0, -1), $lang);
  }
  
  
  function rtrim1_htmlesc($span, $lang, $ch)
  {
    return htmlspecialchars(substr($span, 0, -1));
  }
  
  
  function sch_rtrim1($span, $lang, $ch)
  {
    return substr($span, 0, -1);
  }
  
  
  function rtrim2($span, $lang, $ch)
  {
    return substr($span, 0, -2);
  }
  
  
  function syn_proc($span, $lang, $ch)
  {
    return syntax_highlight_helper($span, $lang);
  }
  
  function dash_putback($span, $lang, $ch)
  {
    return syntax_highlight_helper('-' . $span, $lang);
  }
  
  function slash_putback($span, $lang, $ch)
  {
    return syntax_highlight_helper('/' . $span, $lang);
  }
  
  function slash_putback_rtrim1($span, $lang, $ch)
  {
    return rtrim1('/' . $span, $lang, $ch);
  }
  
  function lparen_putback($span, $lang, $ch)
  {
    return syntax_highlight_helper('(' . $span, $lang);
  }
  
  function lparen_putback_rtrim1($span, $lang, $ch)
  {
    return rtrim1('(' . $span, $lang, $ch);
  }
  
  
  /**
  * Syntax highlight function
  * Does the bulk of the syntax highlighting by lexing the input
  * string, then calling the helper function to highlight keywords.
  */
  function syntax_highlight($text, $language)
  {
    //TODO: Fix these scenarios
    //int, unsigned, double, char, register: <span style='color: #800000'>int</span>
    //string: <span style='color: #dd0000'>"Chris"</span>;
    //<span style='color: #008000'>//comment</span>
    //if, else, return, struct, class, while, do: <strong>if</strong>

    define("normal_text",   1, true);
    define("dq_value",    2, true);
    define("dq_escape",     3, true);
    define("sq_value",    4, true);
    define("sq_escape",     5, true);
    define("slash_begin",   6, true);
    define("star_comment",  7, true);
    define("star_end",      8, true);
    define("line_comment",  9, true);
    define("html_entity",  10, true);
    define("lc_escape",    11, true);
    define("block_comment",12, true);
    define("paren_begin",  13, true);
    define("dash_begin",   14, true);
    define("bt_value",   15, true);
    define("bt_escape",    16, true);
    define("sch_normal",   17, true);
    define("sch_stresc",    18, true);
    define("sch_idexpr",   19, true);
    define("sch_numlit",   20, true);
    define("sch_chrlit",   21, true);
    define("sch_strlit",   22, true);

    $initial_state["Scheme"] = sch_normal;


    $sch[sch_normal][0]     = sch_normal;
    $sch[sch_normal]['"']   = sch_strlit;
    $sch[sch_normal]["#"]   = sch_chrlit;
    $sch[sch_normal]["0"]   = sch_numlit;
    $sch[sch_normal]["1"]   = sch_numlit;
    $sch[sch_normal]["2"]   = sch_numlit;
    $sch[sch_normal]["3"]   = sch_numlit;
    $sch[sch_normal]["4"]   = sch_numlit;
    $sch[sch_normal]["5"]   = sch_numlit;
    $sch[sch_normal]["6"]   = sch_numlit;
    $sch[sch_normal]["7"]   = sch_numlit;
    $sch[sch_normal]["8"]   = sch_numlit;
    $sch[sch_normal]["9"]   = sch_numlit;
    
    $sch[sch_strlit]['"']   = sch_normal;
    $sch[sch_strlit]["\n"]  = sch_normal;
    $sch[sch_strlit]["\\"]  = sch_stresc;
    $sch[sch_strlit][0]     = sch_strlit;
    
    $sch[sch_chrlit][" "]   = sch_normal;
    $sch[sch_chrlit]["\t"]  = sch_normal;
    $sch[sch_chrlit]["\n"]  = sch_normal;
    $sch[sch_chrlit]["\r"]  = sch_normal;
    $sch[sch_chrlit][0]     = sch_chrlit;
    
    $sch[sch_numlit][" "]   = sch_normal;
    $sch[sch_numlit]["\t"]  = sch_normal;
    $sch[sch_numlit]["\n"]  = sch_normal;
    $sch[sch_numlit]["\r"]  = sch_normal;
    $sch[sch_numlit][0]     = sch_numlit;


    
    $cpp[normal_text]["\""] = dq_value;
    $cpp[normal_text]["'"]  = sq_value;
    $cpp[normal_text]["/"]  = slash_begin;
    $cpp[normal_text][0]    = normal_text;

    $cpp[dq_value]["\""]  = normal_text;
    $cpp[dq_value]["\n"]  = normal_text;
    $c89[dq_value]["\\"]  = dq_escape;
    $cpp[dq_value][0]     = dq_value;

    $cpp[dq_escape][0]      = dq_value;

    $cpp[sq_value]["'"]   = normal_text;
    $cpp[sq_value]["\n"]  = normal_text;
    $cpp[sq_value]["\\"]  = sq_escape;
    $cpp[sq_value][0]     = sq_value;

    $cpp[sq_escape][0]      = sq_value;

    $cpp[slash_begin]["*"]  = star_comment;
    $cpp[slash_begin][0]    = normal_text;

    $cpp[star_comment]["*"] = star_end;
    $cpp[star_comment][0]   = star_comment;

    $cpp[star_end]["/"]     = normal_text;
    $cpp[star_end]["*"]     = star_end;
    $cpp[star_end][0]       = star_comment;

    $cpp[slash_begin]["/"]   = line_comment;
    $cpp[line_comment]["\n"] = normal_text;
    $cpp[line_comment]["\\"] = lc_escape;
    $cpp[line_comment][0]    = line_comment;

    $cpp[lc_escape]["\r"]    = lc_escape;
    $cpp[lc_escape][0]       = line_comment;


    $php = $cpp;
    $php[normal_text]["#"]   = line_comment;
    $php[sq_value]["\n"]   = sq_value;
    $php[dq_value]["\n"]   = dq_value;


    $java = $cpp;


    $sql[normal_text]['"']     = dq_value;
    $sql[normal_text]["'"]     = sq_value;
    $sql[normal_text]['`']     = bt_value;
    $sql[normal_text]['-']     = dash_begin;
    $sql[normal_text][0]       = normal_text;

    $sql[dq_value]['"']      = normal_text;
    $sql[dq_value]['\\']     = dq_escape;
    $sql[dq_value][0]        = dq_value;

    $sql[sq_value]["'"]      = normal_text;
    $sql[sq_value]['\\']     = sq_escape;
    $sql[sq_value][0]        = sq_value;

    $sql[bt_value]['`']      = normal_text;
    $sql[bt_value]['\\']     = bt_escape;
    $sql[bt_value][0]        = bt_value;

    $sql[dq_escape][0]         = dq_value;
    $sql[sq_escape][0]         = sq_value;
    $sql[bt_escape][0]         = bt_value;

    $sql[dash_begin]["-"]      = line_comment;
    $sql[dash_begin][0]        = normal_text;

    $sql[line_comment]["\n"]   = normal_text;
    $sql[line_comment]["\\"]   = lc_escape;
    $sql[line_comment][0]      = line_comment;

    $sql[lc_escape]["\r"]      = lc_escape;
    $sql[lc_escape][0]         = line_comment;

    //
    // Main state transition table
    //
    $states = array(
        "c++" => $cpp,
        "php" => $php,
        "java" => $java,
        "sql"  => $sql,
    );


    //
    // Process functions
    //
    $process["c++"][normal_text][sq_value] = "rtrim1";
    $process["c++"][normal_text][dq_value] = "rtrim1";
    $process["c++"][normal_text][slash_begin] = "rtrim1";
    $process["c++"][normal_text][0] = "syn_proc";

    $process["c++"][slash_begin][star_comment] = "rtrim1";
    $process["c++"][slash_begin][0] = "slash_putback";

    $process["sql"][normal_text][sq_value] = "rtrim1";
    $process["sql"][normal_text][dq_value] = "rtrim1";
    $process["sql"][normal_text][bt_value] = "rtrim1";
    $process["sql"][normal_text][dash_begin] = "rtrim1";
    $process["sql"][normal_text][0] = "syn_proc";

    $process["sql"][dash_begin][line_comment] = "rtrim1";
    $process["sql"][dash_begin][0] = "dash_putback";

    $process["c++"][slash_begin][line_comment] = "rtrim1";

    $process["php"] = $process["c++"];
    $process["php"][normal_text][line_comment] = "rtrim1";

    $process["java"] = $process["c++"];


    $process_end["c++"] = "syntax_highlight_helper";
    $process_end["php"] = $process_end["c++"];
    $process_end["java"] = $process_end["c++"];
    $process_end["sql"] = $process_end["c++"];


    //TODO: Replace colour values ("value") to class="string"
    $edges["c++"][normal_text .",". dq_value]   = '<span class="string">"';
    $edges["c++"][normal_text .",". sq_value]   = '<span class="string">\'';
    $edges["c++"][slash_begin .",". star_comment] = '<span class="comment">/*';
    $edges["c++"][dq_value .",". normal_text]   = '</span>';
    $edges["c++"][sq_value .",". normal_text]   = '</span>';
    $edges["c++"][star_end .",". normal_text]     = '</span>';

    $edges["c++"][slash_begin .",". line_comment] = '<span class="comment">//';
    $edges["c++"][line_comment .",". normal_text] = '</span>';
  
    $edges["php"] = $edges["c++"];
    $edges["php"][normal_text .",". line_comment] = '<span class="comment">#';
  
    $edges["java"] = $edges["c++"];


    $edges["sql"][normal_text .",". dq_value]   = '<span class="string">"';
    $edges["sql"][normal_text .",". sq_value]   = '<span class="string">\'';
    $edges["sql"][dash_begin .",". line_comment] = '<span class="comment">--';
    $edges["sql"][normal_text .",". bt_value]   = '`';
    $edges["sql"][dq_value .",". normal_text]   = '</span>';
    $edges["sql"][sq_value .",". normal_text]   = '</span>';
    $edges["sql"][line_comment .",". normal_text] = '</span>';



    //
    // The State Machine
    //
    if (array_key_exists($language, $initial_state))
      $state = $initial_state[$language];
    else
      $state = normal_text;

    $output = "";
    $span = "";
    while (strlen($text) > 0)
    {
      $ch = substr($text, 0, 1);
      $text = substr($text, 1);

      $oldstate = $state;
      $state = (array_key_exists($ch, $states[$language][$state]))?
        $states[$language][$state][$ch] :
        $states[$language][$state][0];

      $span .= $ch;

      if($oldstate != $state)
      {
        if(array_key_exists($language, $process) &&
          array_key_exists($oldstate, $process[$language]))
        {
          if(array_key_exists($state, $process[$language][$oldstate]))
            $pf = $process[$language][$oldstate][$state];
          else
            $pf = $process[$language][$oldstate][0];

          $output .= $pf($span, $language, $ch);
        }
        else
          $output .= $span;

        if(array_key_exists($language, $edges) &&
          array_key_exists("$oldstate,$state", $edges[$language]))
            $output .= $edges[$language]["$oldstate,$state"];

        $span = "";
      }
    }

    if(array_key_exists($language, $process_end) && $state == normal_text)
      $output .= $process_end[$language]($span, $language);
    else
      $output .= $span;

    if($state != normal_text && 
      array_key_exists($language, $edges) && 
      array_key_exists("$state," . normal_text, $edges[$language]))
        $output .= $edges[$language]["$state," . normal_text];
    
    return $output;
  }



  function translate($input, $tabs="")
  {
    //TODO: Only translate code that appears between <code></code> tags
    //TODO: Currency, temperature, mass and distance conversion
    //TODO: 1001 metric hp=736 kW
    //TODO: 8.0 L=7,993 cc=488 in³
    //TODO: 4300 lb=1950 kg
    //TODO: 40.4 L/100 km=5.82 mpg





    //TODO: Link articles to categories
    //TODO: Test article includes <code></code>









    $opening_codeL=$opening_codeR=$closing_codeL=$closing_codeR=false;
    $language="c++";
    $output="";
    
    $i_indicatorL = 0;
    $i_indicatorR = 0;
    $s_tagOption = "";
    $i_arrayCounter = 0;
    $a_html = array();

    $iLeft=0;
    $output="";

    //define("state_normal",    1,  true);
    //define("state_header",    2,  true);
    //define("state_paragraph",  3,  true);
    //
    //$state=state_normal;
    //
    //Loop through and replace \t with &nbsp;
    //In normal text (NOT CODE): Loop through and replace \n\n with </p>\n\n<p> except after </h*>
    //Loop through and replace \n with <br /> except after </h*>
    do
    {
      $iLeft=strpos($input, "<code");

      if(false!==$iLeft)
      {
        $output.=translate_html(substr($input, 0, $iLeft), $tabs);

        $rightbracket=strpos($input, ">", $iLeft);
        if(false!==$rightbracket)
        {
          $parameter = explode(" ", substr($input, $iLeft, $rightbracket-$iLeft));
          foreach($parameter as $pair)
          {
            $parts = explode("=", $pair);
            foreach($parts as $part)
            {
              if("class" == $part[0])
                $language=$part[1];
            }
          }

          //Move to the right of the bracket
          $rightbracket++;

          //Find the end of the code section
          $iRight=strpos($input, "</code>", $iLeft);
        
          if(false!==$iRight)
          {
            $str=translate_lessgreater(substr($input, $rightbracket, $iRight-$rightbracket));
            $str=syntax_highlight($str, $language);
            $output.="<code>\n" . translate_slash_t_to_tab(translate_slash_n_to_br($str)) . "</code>\n";
            $input=substr($input, $iRight+7);
          }
          else
          {
            $str=translate_lessgreater(substr($input, $rightbracket, $iRight-$rightbracket));
            $str=syntax_highlight($str, $language);
            $output.=" <font color=\"red\">NO iRight</font>" . translate_slash_t_to_tab(translate_slash_n_to_br($str));
            $input="";
          }
        }
        else
        {
          $str=translate_lessgreater(substr($input, $rightbracket, $iRight-$rightbracket));
          $str=syntax_highlight($str, $language);
          $output.=" <font color=\"red\">NO rightbracket</font>" . translate_slash_n_to_br($str);
          $input="";
        }
      }
      else
      {
        $output.=translate_html($input, $tabs);
        $input="";
      }

      $iLeft=$rightbracket=$iRight=false;
      $language="c++";
    }while("" != $input);

    return $output;
  }
?>
