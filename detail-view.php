  
<?php include "includes/api.php" ?>
 <?php
    $ex_Id = $_GET['slug'] ?? null;
     if (!$ex_Id) {
        echo "NA";
        exit;
    }
    $data_vars =  fetchApiData('our-excellence/' . BRANCH_ID);

    // Loop to find the matching blog
    $ex___data = $data_vars['data'];
 
    $exData = null;

    foreach ($ex___data as $e__data) {
        if ($e__data['slug'] == $ex_Id) {
            $exData = $e__data;
            break;
        }
    }

    if (!$exData) {
        echo "Blog not found.";
        exit;
    }
    ?>
<html lang="en">

<head>
    <base href="https://allenhousepanki.com/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once __DIR__ . '/shared/includes/cms-page-seo.php';
    cmsResolveAndRenderPageSeo(['data' => $exData], [
        'includes_dir' => __DIR__ . '/includes',
    ]);
    ?>
    <?php include "includes/head.php" ?>
</head>

<body>
 
    <?php include "includes/header.php" ?>
   

    <div class="main relative  mb-[40px] sm:mb-[120px] ">
        <?php
        $detailTitle = (string) ($exData['title'] ?? '');
        cmsRenderPageBanner(['data' => ['page_heading' => $detailTitle, 'title' => $detailTitle]], []);
        ?>

        <div class="flex m-5" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                <li class="inline-flex items-center">
                    <a href="/" class="inline-flex items-center sm:text-sm text-xs font-medium text-blue-main">
                        Home
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="rtl:rotate-180 w-3 h-3 text-blue-main mx-1" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 9 4-4-4-4" />
                        </svg>
                        <a href="view/<?php echo $exData['slug'] ?>" class="ms-1 sm:text-sm text-xs font-medium text-blue-main"><?php echo $exData['title'] ?></a>
                    </div>
                </li>
            </ol>
        </div>

    <div class="main relative sm:top-[20px] mb-[40px] sm:mb-[120px] mx-0 sm:mx-2">
        <div class="mt-8 2xl:w-[1280px] lg:w-[1024px] md:w-[767px] sm:w-[640px] sm:mx-auto  sm:px-5 px-3">
            <div class="mt-10 relative">
                <!-- <div class="absolute top-[-100px] -z-50">
                    <img src="https://res.cloudinary.com/dvzfuapyy/image/upload/v1730307222/Group_53_s3txur.png"
                        class="w-[100%] object-top" alt="">
                </div> -->
                <div class="sm:flex gap-7">
                    <div class="sm:w-[40%]">
                        <img src="<?php echo $exData['medias'][0]['urls'] ?>" alt="" class="w-[100%] sticky top-[100px]" alt="">
                    </div>
                    <div class="sm:w-[60%] sm:mt-0 mt-3">
                <?php
                 foreach($exData['contents'] as $data){
                ?>     
                        <h2 class="text-[22px] font-[700] hr-line relative leading-7 "><?php echo $data['title'] ?></h2>
                        <p class=" text-[16px] text-gray-600 font-[400] mt-2 mb-5">
                          <?php echo $data['description'] ?>
                        </p>
                <?php } ?>        
                    </div>
                </div>
            </div>

            
            </div>
        </div>
    </div>

    <?php include "includes/footer.php" ?>
    <?php include "includes/foot.php" ?>
</body>

</html>