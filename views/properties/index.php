<?php include 'views/components/index-buttons.php'; ?>

<div class="w-4/5 mx-auto mb-8 px-4">
    <!-- Loading -->
    <?php include_once('views/components/loading.php'); ?>

    <div id="property-table" class="flex flex-col">
        <div class="-m-1.5 overflow-x-auto">
            <div class="p-1.5 min-w-full inline-block align-middle">
                <div class="">
                    <table class="min-w-full divide-y divide-gray-200 table-responsive">
                        <thead>
                            <tr>
                                <th scope="col" class="px-4 py-3 text-start">
                                    <label for="hs-at-with-checkboxes-main" class="flex">
                                        <input id="select-all" onclick="toggleCheckboxes(this)" type="checkbox" class="shrink-0 border-gray-300 rounded text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none" id="hs-at-with-checkboxes-main">
                                        <span class="sr-only">Checkbox</span>
                                    </label>
                                </th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase">Actions</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase">Reference</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase">Title Deed</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase max-w-[300px]">Property Details</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase">Unit Type</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase">Size</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase">Price</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase">Unit Status</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase">PF</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase">Bayut</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase">Listing Agent and Owner</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase min-w-[200px]">Published Portals</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase">Created On</th>
                            </tr>
                        </thead>
                        <tbody id="property-list" class="divide-y divide-gray-200"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <?php include 'views/components/pagination.php'; ?>

    <!-- Modals -->
    <?php include 'views/modals/filter.php'; ?>
    <?php include 'views/modals/refresh-listing.php'; ?>
    <?php
    if ($isAdmin) {
        include 'views/modals/transfer-to-agent.php';
        include 'views/modals/transfer-to-owner.php';
    }
    ?>
</div>


<script>
    let currentPage = 1;
    const pageSize = 50;
    let totalPages = 0;

    (async () => {
        const isAdmin = localStorage.getItem('isAdmin') == 'true';
        const pfXmlId = await getPlXmlId(localStorage.getItem('userId'));

        console.log('pfXmlId', pfXmlId);
        localStorage.setItem('pfXmlId', pfXmlId);
    })();

    const isAdmin = localStorage.getItem('isAdmin') == 'true';
    const pfXmlId = localStorage.getItem('pfXmlId');

    async function getPlXmlId(userId) {
        try {
            const response = await fetch(`${API_BASE_URL}/user.get?ID=${userId}&select[0]=UF_USR_1745847119672`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            return data.result[0]?.UF_USR_1745847119672 ?? null;
        } catch (error) {
            console.error('Error fetching properties:', error);
            return null;
        }
    }



    async function fetchProperties(page = 1, filters = null) {
        const baseUrl = API_BASE_URL;
        const entityTypeId = LISTINGS_ENTITY_TYPE_ID;
        const fields = [
            'id', 'ufCrm18ReferenceNumber', 'ufCrm18OfferingType', 'ufCrm18PropertyType', 'ufCrm18Price', 'ufCrm18TitleEn', 'ufCrm18DescriptionEn', 'ufCrm18Size', 'ufCrm18Bedroom', 'ufCrm18Bathroom', 'ufCrm18PhotoLinks', 'ufCrm18AgentName', 'ufCrm18City', 'ufCrm18Community', 'ufCrm18SubCommunity', 'ufCrm18Tower', 'ufCrm18BayutCity', 'ufCrm18BayutCommunity', 'ufCrm18BayutSubCommunity', 'ufCrm18BayutTower', 'ufCrm18PfEnable', 'ufCrm18BayutEnable', 'ufCrm18DubizzleEnable', 'ufCrm18WebsiteEnable', 'ufCrm18ListingOwner', 'ufCrm18Status', 'ufCrm18RentalPeriod', 'createdTime', 'ufCrm18TitleDeed'
        ];
        const orderBy = {
            id: 'desc'
        };
        const start = (page - 1) * pageSize;

        function buildApiUrl(baseUrl, entityTypeId, fields, orderBy, start, filters) {
            const selectParams = fields.map((field, index) => `select[${index}]=${field}`).join('&');

            const orderParams = Object.entries(orderBy)
                .map(([key, value]) => `order[${key}]=${value}`)
                .join('&');

            if (filters) {
                sessionStorage.setItem('filters', JSON.stringify(filters));

                const filterParams = Object.entries(filters)
                    .map(([key, value]) => `filter[${key}]=${value}`)
                    .join('&');

                return `${baseUrl}/crm.item.list?entityTypeId=${entityTypeId}&${selectParams}&${orderParams}&start=${start}&${filterParams}`;
            }

            return `${baseUrl}/crm.item.list?entityTypeId=${entityTypeId}&${selectParams}&${orderParams}&start=${start}`;
        }

        // Generate the API URL
        const apiUrl = buildApiUrl(baseUrl, entityTypeId, fields, orderBy, start, filters);

        const loading = document.getElementById('loading');
        const propertyTable = document.getElementById('property-table');
        const propertyList = document.getElementById('property-list');
        const pagination = document.getElementById('pagination');
        const prevPage = document.getElementById('prevPage');
        const nextPage = document.getElementById('nextPage');
        const pageInfo = document.getElementById('pageInfo');

        try {
            loading.classList.remove('hidden');
            propertyTable.classList.add('hidden');
            pagination.classList.add('hidden');


            const response = await fetch(apiUrl, {
                method: 'GET'
            });

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const data = await response.json();
            const properties = data.result?.items || [];
            const totalCount = data.total || 0;

            document.getElementById('listingCount').textContent = totalCount

            totalPages = Math.ceil(totalCount / pageSize);

            prevPage.disabled = page === 1;
            nextPage.disabled = page === totalPages || totalPages === 0;
            pageInfo.textContent = `Page ${page} of ${totalPages}`;

            propertyList.innerHTML = properties
                .map(
                    (property) => `
                <tr>
                    <td class="size-sm whitespace-nowrap">
                        <div class="ps-6 py-3">
                            <label for="hs-at-with-checkboxes-1" class="flex">
                            <input type="checkbox" name="property_ids[]" value="${property.id}" class="shrink-0 border-gray-300 rounded text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none" id="hs-at-with-checkboxes-1">
                            <span class="sr-only">Checkbox</span>
                            </label>
                        </div>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-transparent dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <ul class="dropdown-menu shadow absolute z-10" style="max-height: 60vh; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #6B7280 #f9fafb; font-size:medium;">
                                ${isAdmin ? `
                                <li><a class="dropdown-item" href="?page=edit-property&id=${property.id}"><i class="fa-solid fa-edit me-2"></i>Edit</a></li>
                                <li><button class="dropdown-item" onclick="handleAction('duplicate', ${property.id})"><i class="fa-solid fa-copy me-2"></i>Duplicate Listing</button></li>
                                <li>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#referenceModal" data-property-id="${property.id}" data-reference="${property.ufCrm18ReferenceNumber}" data-status="${property.ufCrm18Status}">
                                        <i class="fa-solid fa-sync me-2"></i>Refresh Listing
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                ` : ''}
                                <li><a class="dropdown-item" target="_blank" href="download-pdf.php?type=logged&id=${property.id}"><i class="fa-solid fa-print me-2"></i>Download PDF as Logged-In Agent</a></li>
                                <li><a class="dropdown-item" target="_blank" href="download-pdf.php?type=agent&id=${property.id}"><i class="fa-solid fa-print me-2"></i>Download PDF as Listing Agent</a></li>
                                <li><a class="dropdown-item" target="_blank" href="download-pdf.php?type=owner&id=${property.id}"><i class="fa-solid fa-print me-2"></i>Download PDF as Listing Owner</a></li>
                                ${isAdmin ? `
                                <li><hr class="dropdown-divider"></li>
                                <li><button class="dropdown-item" onclick="handleAction('export-excel', ${property.id})"><i class="fa-solid fa-file-excel me-2"></i>Export as Excel</button></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><button class="dropdown-item" onclick="handleAction('publish', ${property.id})"><i class="fa-solid fa-bullhorn me-2"></i>Publish to all</button></li>
                                <li><button class="dropdown-item" onclick="handleAction('publish', ${property.id}, 'pf')"><i class="fa-solid fa-search me-2"></i>Publish to PF</button></li>
                                <li><button class="dropdown-item" onclick="handleAction('publish', ${property.id}, 'bayut')"><i class="fa-solid fa-building me-2"></i>Publish to Bayut</button></li>
                                <li><button class="dropdown-item" onclick="handleAction('publish', ${property.id}, 'dubizzle')"><i class="fa-solid fa-home me-2"></i>Publish to Dubizzle</button></li>
                                <li><button class="dropdown-item" onclick="handleAction('publish', ${property.id}, 'website')"><i class="fa-solid fa-globe me-2"></i>Publish to Website</button></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><button class="dropdown-item" onclick="handleAction('unpublish', ${property.id})"><i class="fa-solid fa-archive me-2"></i>Unpublish from all</button></li>
                                <li><button class="dropdown-item" onclick="handleAction('unpublish', ${property.id}, 'pf')"><i class="fa-solid fa-search me-2"></i>Unpublish from PF</button></li>
                                <li><button class="dropdown-item" onclick="handleAction('unpublish', ${property.id}, 'bayut')"><i class="fa-solid fa-building me-2"></i>Unpublish from Bayut</button></li>
                                <li><button class="dropdown-item" onclick="handleAction('unpublish', ${property.id}, 'dubizzle')"><i class="fa-solid fa-home me-2"></i>Unpublish from Dubizzle</button></li>
                                <li><button class="dropdown-item" onclick="handleAction('unpublish', ${property.id}, 'website')"><i class="fa-solid fa-globe me-2"></i>Unpublish from Website</button></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><button class="dropdown-item text-danger" onclick="handleAction('archive', ${property.id})"><i class="fa-solid fa-archive me-2"></i>Archive</button></li>
                                <li><button class="dropdown-item text-danger" onclick="handleAction('delete', ${property.id})"><i class="fa-solid fa-trash me-2"></i>Delete</button></li>
                                ` : ''}
                            </ul>
                        </div>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800 text-wrap">${property.ufCrm18ReferenceNumber || 'N/A'}</td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800 text-wrap">${property.ufCrm18TitleDeed || ''}</td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800 min-w-[300px]">
                        <div class="flex">
                            <img class="w-20 h-20 rounded object-cover mr-4" src="${property.ufCrm18PhotoLinks[0] || 'https://placehold.jp/150x150.png'}" alt="${property.ufCrm18TitleEn || 'N/A'}">
                            <div class="text-sm">
                                <p class="text-gray-800 font-semibold"><a class="hover:text-black/75 text-black text-semibold text-wrap text-decoration-none" href="?page=view-property&id=${property.id}">${property.ufCrm18TitleEn || 'N/A'}</a></p>
                                <p class="text-gray-400 text-wrap max-w-full truncate">${property.ufCrm18DescriptionEn.slice(0, 60) + '...' || 'N/A'}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800">
                        <div class="flex flex-col items-start gap-1">
                            <span class="text-sm text-muted" title="Bathrooms"><i class="fa-solid fa-bath mr-1"></i>${property.ufCrm18Bathroom || 'N/A'}</span>
                            <span class="text-sm text-muted" title="Bedrooms"><i class="fa-solid fa-bed mr-1"></i>${property.ufCrm18Bedroom === 0 ? 'Studio' : property.ufCrm18Bedroom === 11 ? '10+' : property.ufCrm18Bedroom || 'N/A'}</span>
                        </div>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800">
                        <div class="flex flex-col items-start gap-1">
                            <span class="text-sm text-muted" title="Bathrooms"><i class="fa-solid fa-ruler-combined mr-1"></i>${property.ufCrm18Size + ' sqft' || 'N/A'}</span>
                            <span class="text-sm text-muted" title="Bedrooms"><i class="fa-solid fa-ruler-horizontal mr-1"></i>${sqftToSqm(property.ufCrm18Size) + ' sqm' || 'N/A'}</span>
                        </div>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800">
                        ${
                            property.ufCrm18Price 
                                ? `${formatPrice(property.ufCrm18Price)}${property.ufCrm18OfferingType === 'RR' || property.ufCrm18OfferingType === 'CR' 
                                    ? `/${property.ufCrm18RentalPeriod === 'Y' ? 'Year' : property.ufCrm18RentalPeriod === 'M' ? 'Month' : property.ufCrm18RentalPeriod === 'W' ? 'Week' : property.ufCrm18RentalPeriod === 'D' ? 'Day' : ''} - Rent`
                                    : (property.ufCrm18OfferingType === 'CS' || property.ufCrm18OfferingType === 'RS' ? ' - Sale' : '')}`
                                : ''
                        }
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800">
                        ${getStatusBadge(property.ufCrm18Status)}
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800">
                        <p>
                            ${[
                                property.ufCrm18City,
                                property.ufCrm18Community,
                            ]
                            .filter(Boolean)
                            .join(' - ') || ''}
                        </p>
                        <p>
                            ${[
                                property.ufCrm18SubCommunity,
                                property.ufCrm18Tower
                            ]
                            .filter(Boolean)
                            .join(' - ') || ''}
                        </p>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800">
                        <p>
                            ${[
                                property.ufCrm18BayutCity,
                                property.ufCrm18BayutCommunity,
                            ]
                            .filter(Boolean)
                            .join(' - ') || ''}
                        </p>
                        <p>
                            ${[
                                property.ufCrm18BayutSubCommunity,
                                property.ufCrm18BayutTower
                            ]
                            .filter(Boolean)
                            .join(' - ') || ''}
                        </p>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800">
                        <p class="">${property.ufCrm18AgentName || ''}</p> 
                        <p class="">${property.ufCrm18ListingOwner || ''}</p> 
                    </td>
                   <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800">
                        <div class="flex gap-1">
                            ${property.ufCrm18PfEnable === "Y" ? '<img class="w-8 h-8 rounded-full object-cover" src="assets/images/pf.png" alt="Property Finder" title="Property Finder">' : ''}
                            ${property.ufCrm18BayutEnable === "Y" ? '<img class="w-8 h-8 rounded-full object-cover" src="assets/images/bayut.png" alt="Bayut" title="Bayut">' : ''}
                            ${property.ufCrm18DubizzleEnable === "Y" ? '<img class="w-8 h-8 rounded-full object-cover" src="assets/images/dubizzle.png" alt="Dubizzle" title="Dubizzle">' : ''}
                            ${property.ufCrm18WebsiteEnable === "Y" ? '<img class="w-8 h-8 rounded-full object-cover bg-black" src="assets/images/company-logo.png" alt="X10 Real Estate" title="X10 Real Estate">' : ''}
                        </div>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800">
                        <p class="">${formatDate(property.createdTime) || ''}</p> 
                    </td>

                </tr>`
                )
                .join('');

            return properties;
        } catch (error) {
            console.error('Error fetching properties:', error);
            return [];
        } finally {
            loading.classList.add('hidden');
            propertyTable.classList.remove('hidden');
            pagination.classList.remove('hidden');

        }
    }

    function changePage(direction) {
        if (direction === 'prev' && currentPage > 1) {
            currentPage--;
        } else if (direction === 'next' && currentPage < totalPages) {
            currentPage++;
        }
        const filters = JSON.parse(sessionStorage.getItem('filters'));
        fetchProperties(currentPage, isAdmin ? filters : {
            ...filters,
            "ufCrm18AgentId": localStorage.getItem('pfXmlId')
        });
    }

    function toggleCheckboxes(source) {
        const checkboxes = document.querySelectorAll('input[name="property_ids[]"]');

        checkboxes.forEach((checkbox) => {
            checkbox.checked = source.checked;
        });
    }

    function formatPrice(amount, locale = 'en-US', currency = 'AED') {
        if (isNaN(amount)) {
            return 'Invalid amount';
        }

        return new Intl.NumberFormat(locale, {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    function getStatusBadge(status) {
        switch (status) {
            case 'PUBLISHED':
                return '<span class="inline-flex items-center gap-x-1.5 py-1.5 px-2 border rounded-full text-xs font-medium bg-green-50 text-green-800">Published</span>';
            case 'UNPUBLISHED':
                return '<span class="inline-flex items-center gap-x-1.5 py-1.5 px-2 border rounded-full text-xs font-medium bg-red-50 text-red-800">Unpublished</span>';
            case 'LIVE':
                return '<span class="inline-flex items-center gap-x-1.5 py-1.5 px-2 border rounded-full text-xs font-medium bg-blue-50 text-blue-800">Live</span>';
            case 'DRAFT':
                return '<span class="inline-flex items-center gap-x-1.5 py-1.5 px-2 border rounded-full text-xs font-medium bg-gray-50 text-gray-800">Draft</span>';
            case 'ARCHIVED':
                return '<span class="inline-flex items-center gap-x-1.5 py-1.5 px-2 border rounded-full text-xs font-medium bg-gray-50 text-gray-800">Archived</span>';
            default:
                return '<span class="inline-flex items-center gap-x-1.5 py-1.5 px-2 border rounded-full text-xs font-medium bg-gray-50 text-gray-800">' + status + '</span>';
        }
    }

    fetchProperties(currentPage, isAdmin ? null : {
        "ufCrm18AgentId": localStorage.getItem('pfXmlId')
    });
</script>