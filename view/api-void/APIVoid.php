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

            /*API Result CSS*/
            /*Theme color*/
            .safe {
                border: 1px solid #18bc9c;
                background-color: #e5ffe5;
            }

            .safe h3 {
                background-color: #18bc9c;
                color: white;
            }

            .safe h4 {
                color: #18bc9c;
            }

            .safe .icon svg {
                fill: #18bc9c;
            }

            .safe svg.danger {
                display: none;
            }


            .danger {
                border: 1px solid #f39c12;
                background-color: #fffae5;
            }

            .danger h3 {
                background-color: #f39c12;
                color: white;
            }

            .danger h4 {
                color: #f39c12;
            }

            .danger .icon svg {
                fill: #f39c12;
            }

            .danger svg.safe {
                display: none;
            }


            .summarized-decision {
                border-radius: 8px;
                overflow: hidden;
            }

            .summarized-decision h3 {
                margin: 0;
                padding: 15px 20px;
                font-size: 22px;
            }

            .summarized-decision h4 {
                margin: 15px 0 0 0;
                padding: 15px 0;
                font-size: 20px;
            }

            .summarized-decision p {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .summarized-decision .icon svg {
                position: relative;
                width: 50px;
                height: 50px;
                top: 5px;
                border: none;
            }

            .decision-body {
                padding: 0 20px;
            }

            .decision-body p {
                margin: 10px 0 0 0;
                padding-bottom: 20px;
            }

            .decision-body strong {
                display: inline-block;
            }

            .decision-body .body-description {
                display: flex;
                flex-direction: column;
            }

            .decision-sub-header {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            #tableContainer {
                display: flex;
                flex-direction: column;
                gap: 40px;
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
             * Format data for printing
             * @param data
             * @returns {{}}
             */
            function readyText(data) {
                const formatedData = {};
                if (!Array.isArray(data)) {
                    Object.keys(data).forEach(key => {
                        const splitKey = key.split('_');
                        if (splitKey[0] === "is") {
                            splitKey.shift();
                        }
                        const newKey = splitKey.join(" ");
                        data[key] !== "" && (
                            formatedData[newKey] = data[key] === false ? "no" : (
                                data[key] === true ? "yes" : data[key]
                            )
                        );
                    })
                }
                return formatedData;
            }

            /**
             * Create Table
             * @param title
             * @param data
             */
            function createTable(title, data) {
                if (Array.isArray(data)) {
                    data.forEach(datum => {
                        createTable(title, datum);
                    })
                    return;
                }
                const dataObj = readyText(data);
                const container = document.createElement('div');
                const html = `
                <div class="summarized-decision safe">
                  <h3>${title}</h3>
                  <div class="decision-body">
<!--                    <div class="decision-sub-header">-->
<!--                      <div class="icon">-->
<!--                        <svg-->
<!--                        class="safe"-->
<!--                          xmlns="http://www.w3.org/2000/svg"-->
<!--                          width="24"-->
<!--                          height="24"-->
<!--                          viewBox="0 0 24 24"-->
<!--                        >-->
<!--                          <path-->
<!--                            d="M4 21h1V8H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2zM20 8h-7l1.122-3.368A2 2 0 0 0 12.225 2H12L7 7.438V21h11l3.912-8.596L22 12v-2a2 2 0 0 0-2-2z"-->
<!--                          ></path>-->
<!--                        </svg>-->

<!--                        <svg-->
<!--                        class="danger"-->
<!--                          xmlns="http://www.w3.org/2000/svg"-->
<!--                          width="24"-->
<!--                          height="24"-->
<!--                          viewBox="0 0 24 24"-->
<!--                        >-->
<!--                          <path-->
<!--                            d="M11.953 2C6.465 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.493 2 11.953 2zM13 17h-2v-2h2v2zm0-4h-2V7h2v6z"-->
<!--                          ></path>-->
<!--                        </svg>-->
<!--                      </div>-->
<!--                      <div>-->
<!--                        <h4>Potentially Legit</h4>-->
<!--                      </div>-->
<!--                    </div>-->
                    <div class="body-description">
                      ${Object.keys(dataObj).map(key => {
                    return `<p><strong>${key}:</strong> ${dataObj[key]}</p>`;
                }).join("\n")}
                    </div>
                  </div>
                </div>
                `;
                console.log(data);
                container.innerHTML = html;

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

        $result = $wpdb->get_results("SELECT * FROM $table_name ORDER BY time DESC LIMIT 100");
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
                justify-content: flex-start;
                gap: var(--reputation-site-gap);
                margin-bottom: 40px;
                flex-wrap: wrap;
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
            <?php
            foreach ($result as $website_data) :

                ?>
                <div class="reputation_site">
                    <div>
                        <div class="reputation_domain_icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
                                <path d="M320 464c8.8 0 16-7.2 16-16l0-288-80 0c-17.7 0-32-14.3-32-32l0-80L64 48c-8.8 0-16 7.2-16 16l0 384c0 8.8 7.2 16 16 16l256 0zM0 64C0 28.7 28.7 0 64 0L229.5 0c17 0 33.3 6.7 45.3 18.7l90.5 90.5c12 12 18.7 28.3 18.7 45.3L384 448c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64L0 64z"/>
                            </svg>
                        </div>
                        <div class="reputation_domain_name"><a
                                    href="https://reportscammedfunds.com/website-reputation-checker/?url=<?php echo $website_data->url; ?>"><?php echo $website_data->url; ?></a>
                        </div>
                    </div>
                    <div>
                        <div class="reputation_domain_time">
                            <?php echo $this->time_ago($website_data->time); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();

    }

    public function time_ago($datetime)
    {
        $currentTime = new DateTime();
        $givenTime = new DateTime($datetime);
        $interval = $currentTime->diff($givenTime);

        if ($interval->y > 0) {
            return $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
        } elseif ($interval->m > 0) {
            return $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
        } elseif ($interval->d > 0) {
            return $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
        } elseif ($interval->h > 0) {
            return $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
        } elseif ($interval->i > 0) {
            return $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
        } else {
            return $interval->s . ' second' . ($interval->s > 1 ? 's' : '') . ' ago';
        }
    }
};