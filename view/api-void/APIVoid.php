<?php
// if accessed directly
if (!defined("ABSPATH")) {
    exit;
}
new class {
    public function __construct()
    {
        add_shortcode("API_VOID_VIEWS", [$this, 'shortcode']);
    }

    /**
     * API Void output shortcode
     * @return false|string
     */
    public function shortcode(){

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
        <p class="description">Check if a domain (e.g google.com) is blacklisted with this online domain reputation check tool.
            A free online domain risk score tool you can use to get reputation of a
            domain. If you're concerned about a domain, this tool can help you find out if the domain is malicious. Simply enter the domain name in the form below and press the button.</p>
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


            const form = document.querySelector('#api-void');
            form.addEventListener("submit", async e => {
                // clear previous tables and prevent default
                e.preventDefault();
                document.querySelector("#tableContainer").innerHTML = "";

                // add loading effect
                document.querySelector('#api-void input[type="submit"]').setAttribute("value", "Please Wait");
                document.querySelector('#api-void input[type="submit"]').setAttribute("disabled", "");

                const domain = e.target.domainSearch.value;
                // call api
                const res = await fetch("https://reportscammedfunds.com/wp-json/raw/v1/api-void?url=" + domain);
                const result = await res.json();

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
            });
        </script>
        <?php
        return ob_get_clean();
    }
};