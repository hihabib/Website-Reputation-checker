<?php
// if accessed directly
if (!defined("ABSPATH")) {
    exit;
}
new class {
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_api_void_rest_api']);
        add_shortcode("API_VOID_VIEWS", [$this, 'api_void_short_code']);
        add_shortcode("API_VOID_RECENT_CHECKS", [$this, 'recent_checks_shortcode']);
    }

    /**
     * API Void output shortcode
     * @return false|string
     */
    public function api_void_short_code()
    {

        ob_start();
        ?>
        <style>

            .verification_badge, .short-description {
                display: none;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }

            th, td {
                padding: 8px 12px;
                border: 1px solid #ccc;
                text-align: left;
            }

            th {
                background-color: #f4f4f4;
            }

            tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            #api-void > div {
                display: flex;
                flex-direction: column;
                gap: 20px;
                margin: 40px 0;
            }

            #api-void input {
                width: 100%;
            }

            #api-void input[type="text"] {
                padding: 12px 10px;
                border: 1px solid rgb(32, 92, 212);
                border-radius: 10px;
            }

            #api-void input[type="submit"] {
                padding-bottom: 12px;
                border: none;
                font-weight: 500;
                padding-left: 24px;
                padding-right: 24px;
                padding-top: 12px;
                color: rgb(255, 255, 255);
                background-color: rgb(32, 92, 212);
                box-shadow: none;
                cursor: pointer;
            }

            #api-void input[disabled], #api-void input[disabled]:hover {
                background: lightgray;
                cursor: not-allowed;
            }

            .entry-title {
                padding: 30px 0 10px 0;
                display: block;
                text-align: center;
            }

            .description {
                width: 80%;
                display: block;
                margin: 0 auto;
                text-align: center;
            }
        </style>
        <p class="description">Check if a domain (e.g google.com) is blacklisted with this online domain reputation
            check tool.
            A free online domain risk score tool you can use to get reputation of a
            domain. If you're concerned about a domain, this tool can help you find out if the domain is malicious.
            Simply enter the domain name in the form below and press the button.</p>
        <form id="api-void" action="<?php the_permalink(); ?>">
            <div>
                <div>
                    <input id="domainSearch" name="domainSearch" type="text" placeholder="Enter Your Domain">
                </div>
                <div>
                    <input type="submit" value="Search">
                </div>
            </div>
        </form>
        <div id="tableContainer">

        </div>

        <script>

            /**
             * Create Table
             * @param title
             * @param data
             */
            function createTable(title, data) {
                const container = document.createElement('div');
                const heading = document.createElement('h2');
                heading.textContent = title;
                container.appendChild(heading);

                const table = document.createElement('table');
                // Check if data is an array or object
                if (Array.isArray(data)) {
                    data.forEach((item, index) => {
                        Object.keys(item).forEach(key => {
                            const row = document.createElement('tr');

                            const cellKey = document.createElement('th');
                            cellKey.textContent = key;
                            row.appendChild(cellKey);

                            const cellValue = document.createElement('td');
                            cellValue.textContent = item[key];
                            row.appendChild(cellValue);

                            table.appendChild(row);
                        });
                    });
                } else {
                    Object.keys(data).forEach(key => {
                        const row = document.createElement('tr');

                        const cellKey = document.createElement('th');
                        cellKey.textContent = key;
                        row.appendChild(cellKey);

                        const cellValue = document.createElement('td');
                        cellValue.textContent = JSON.stringify(data[key], null, 2);
                        row.appendChild(cellValue);

                        table.appendChild(row);
                    });

                }

                container.appendChild(table);

                // remove loading effect
                if (document.querySelector('#api-void input[type="submit"]').value !== "Search") {
                    document.querySelector('#api-void input[type="submit"]').setAttribute("value", "Search");
                    document.querySelector('#api-void input[type="submit"]').removeAttribute("disabled");
                }

                // add table
                document.querySelector("#tableContainer").appendChild(container);
            }

            /**
             * Process API  Void.
             * This function will call api void to get data
             * This function will save searching domain to database
             * This function will call createTable function to manipulate DOM
             * @returns {Promise<void>}
             */
            async function processAPIVoid(domain) {
                document.querySelector("#tableContainer").innerHTML = "";
                // add loading effect
                document.querySelector('#api-void input[type="submit"]').setAttribute("value", "Please Wait");
                document.querySelector('#api-void input[type="submit"]').setAttribute("disabled", "");

                // call api-void
                const res = await fetch("https://reportscammedfunds.com/wp-json/raw/v1/api-void?url=" + domain);
                const result = await res.json();

                // save url to db
                const saveUrlResponse = await fetch("https://reportscammedfunds.com/wp-json/api-void/v1/save-search-url", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        url: domain
                    })
                });
                const saveUrlResult = await saveUrlResponse.json();
                console.log(saveUrlResult)

                // create tables
                if (result.error === undefined) {
                    createTable("Domain Age", result?.data?.report?.domain_age ?? {});
                    createTable("DNS Records - NS", result?.data?.report?.dns_records?.ns?.records ?? {});
                    createTable("DNS Records - MX", result?.data?.report?.dns_records?.mx?.records ?? {});
                    createTable("Security Checks", result?.data?.report?.security_checks ?? {});
                    createTable("Server Details", result?.data?.report?.server_details ?? {});
                    createTable("URL Parts", result?.data?.report?.url_parts ?? {});
                    createTable("Web Page", result?.data?.report?.web_page ?? {});
                } else {
                    createTable(result.error, result ?? {});
                }
            }

            // submit form
            /**
             * @type HTMLFormElement
             */
            const form = document.querySelector('#api-void');
            form.addEventListener("submit", e => {
                // clear previous tables and prevent default
                e.preventDefault();
                const domain = e.target.domainSearch.value;
                processAPIVoid(domain);
            });

            if (location.search !== "") {
                /**
                 * @type HTMLInputElement
                 */
                const input = document.querySelector(`[name="domainSearch"]`);
                input.value = location.search.split("?url=")[1];
                processAPIVoid(input.value);
            }
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * endpoint: domain.com/wp-json/api-void/v1/save-search-url
     * @return void
     */
    public function register_api_void_rest_api()
    {
        register_rest_route("api-void/v1", "/save-search-url", [
            'methods' => "POST",
            "callback" => [$this, 'save_recenter_search_api_void'],
            'permission_callback' => '__return_true'
        ]);
    }

    /**
     * Insert URL to db
     * @param $data
     * @return WP_Error|WP_HTTP_Response|WP_REST_Response
     */
    public function save_recenter_search_api_void($data)
    {
        // check is url provided or not
        if (empty($data['url'])) {
            return new WP_Error('no_url', "No URL provided to save", ['status' => 400]);
        }

        global $wpdb;

        // db table name
        $db_prefix = $wpdb->prefix;
        $table_name = $db_prefix . "api_void_search_history";

        $is_updated = $wpdb->update($table_name, [
            'time' => date("Y-m-d H:i:s")
        ], [
            'url' => $data['url'],
        ]);


        if (!$is_updated) {
            // insert data
            $data_to_save = [
                'url' => $data['url'],
                'time' => date("Y-m-d H:i:s")
            ];
            $is_inserted = $wpdb->insert($table_name, $data_to_save);
            // rest response
            if ($is_inserted) {
                return rest_ensure_response(['updated' => 'false', ...$data_to_save]);
            } else {
                return new WP_Error('not_saved', 'something went wrong', ['status' => 500]);
            }
        } else {
            return rest_ensure_response(
                [
                    'updated' => 'true',
                    'url' => $data['url'],
                    'time' => date("Y-m-d H:i:s")
                ]
            );
        }

    }

    /**
     * @return string
     */
    public function recent_checks_shortcode()
    {
        global $wpdb;
        $table_prefix = $wpdb->prefix;
        $table_name = $table_prefix . "api_void_search_history";

        $result = $wpdb->get_results("SELECT * FROM $table_name");
        ob_start();
        if ($result === null) {
            echo "<h3>No data is stored</h3>";
            return ob_get_clean();
        }
        ?>
        <style>
            :root {
                --reputation-site-gap: 15px;
                --reputation-site-col: 3;
                --single-col-width: calc(calc(100% / var(--reputation-site-col)) - calc(calc(var(--reputation-site-gap) * calc(var(--reputation-site-col) - 1)) / var(--reputation-site-col)))
            }

            .recent_reputation_checks {
                display: flex;
                justify-content: space-between;
                gap: var(--reputation-site-gap);
            }

            .reputation_site {
                width: var(--single-col-width);
                border: 1px solid lightgrey;
                border-radius: 8px;
                padding: 10px
            }
            .reputation_domain_icon {
                width: 15px;
                margin-top: 8px;
            }
            div:has(>.reputation_domain_icon) {
                display: flex;
                gap: 10px;
                align-items: center;
            }
        </style>
        <div class="recent_reputation_checks">
            <div class="reputation_site">
                <div>
                    <div class="reputation_domain_icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
                            <!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                            <path d="M320 464c8.8 0 16-7.2 16-16l0-288-80 0c-17.7 0-32-14.3-32-32l0-80L64 48c-8.8 0-16 7.2-16 16l0 384c0 8.8 7.2 16 16 16l256 0zM0 64C0 28.7 28.7 0 64 0L229.5 0c17 0 33.3 6.7 45.3 18.7l90.5 90.5c12 12 18.7 28.3 18.7 45.3L384 448c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64L0 64z"/>
                        </svg>
                    </div>
                    <div class="reputation_domain_name">just-fly.aero</div>
                </div>
                <div>
                    <div class="reputation_domain_time">
                        3 minutes ago
                    </div>
                </div>
            </div>
            <div class="reputation_site">
                <div>
                    <div class="reputation_domain_icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
                            <!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                            <path d="M320 464c8.8 0 16-7.2 16-16l0-288-80 0c-17.7 0-32-14.3-32-32l0-80L64 48c-8.8 0-16 7.2-16 16l0 384c0 8.8 7.2 16 16 16l256 0zM0 64C0 28.7 28.7 0 64 0L229.5 0c17 0 33.3 6.7 45.3 18.7l90.5 90.5c12 12 18.7 28.3 18.7 45.3L384 448c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64L0 64z"/>
                        </svg>
                    </div>
                    <div class="reputation_domain_name">just-fly.aero</div>
                </div>
                <div>
                    <div class="reputation_domain_time">
                        3 minutes ago
                    </div>
                </div>
            </div>
            <div class="reputation_site">
                <div>
                    <div class="reputation_domain_icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
                            <!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                            <path d="M320 464c8.8 0 16-7.2 16-16l0-288-80 0c-17.7 0-32-14.3-32-32l0-80L64 48c-8.8 0-16 7.2-16 16l0 384c0 8.8 7.2 16 16 16l256 0zM0 64C0 28.7 28.7 0 64 0L229.5 0c17 0 33.3 6.7 45.3 18.7l90.5 90.5c12 12 18.7 28.3 18.7 45.3L384 448c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64L0 64z"/>
                        </svg>
                    </div>
                    <div class="reputation_domain_name">just-fly.aero</div>
                </div>
                <div>
                    <div class="reputation_domain_time">
                        3 minutes ago
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();

    }
};